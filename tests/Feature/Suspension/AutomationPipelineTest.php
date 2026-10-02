<?php

namespace Tests\Feature\Suspension;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Enums\SuspendAction;
use App\Enums\SuspendLogStatus;
use App\Enums\SuspensionSource;
use App\Enums\WhatsappStatus;
use App\Jobs\ScanOverdueInvoices;
use App\Jobs\SendWhatsappNotification;
use App\Jobs\SuspendCustomer;
use App\Mikrotik\RouterClientFactory;
use App\Models\Pelanggan;
use App\Models\SuspendLog;
use App\Models\Tagihan;
use App\Models\Tenant;
use App\Models\WhatsappNotification;
use App\Suspension\CustomerSuspender;
use App\Suspension\OverdueScanRunner;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Support\FakeRouterClient;
use Tests\Support\FakeRouterClientFactory;
use Tests\Support\InteractsWithTenant;
use Tests\TestCase;

/**
 * Menjalankan satu putaran automasi dari awal sampai akhir, melewati queue.
 *
 * Test ini sengaja tidak memakai Queue::fake() di bagian akhir, karena
 * serialisasi job adalah tempat dua bug pernah muncul: property readonly
 * tidak bisa dipulihkan, dan nama tabel model yang salah tidak ketahuan
 * sampai job benar-benar dieksekusi worker.
 */
class AutomationPipelineTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    private Tenant $tenant;

    private FakeRouterClient $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new FakeRouterClient;

        $this->app->bind(
            RouterClientFactory::class,
            fn (): FakeRouterClientFactory => new FakeRouterClientFactory($this->router),
        );

        $this->tenant = Tenant::factory()->create(['name' => 'ISP Pipeline']);
    }

    public function test_overdue_invoice_suspends_the_customer_and_queues_a_notification(): void
    {
        $customer = $this->inTenant($this->tenant, function (): Pelanggan {
            $customer = $this->makeSuspendableCustomer($this->tenant, [
                'name' => 'Siti Aminah',
                'whatsapp_number' => '081298765432',
            ]);

            $this->makeInvoice($customer, [
                'invoice_number' => 'INV-OVERDUE-1',
                'status' => InvoiceStatus::BelumBayar,
                'due_date' => now()->subDays(20)->toDateString(),
                'total' => 275000,
            ]);

            return $customer;
        });

        // 1. Job harian menemukan dan menjadwalkan suspend.
        Queue::fake();

        (new ScanOverdueInvoices)->handle(app(OverdueScanRunner::class));

        Queue::assertPushed(SuspendCustomer::class, 1);

        // Notifikasi belum dibuat: pesan hanya boleh dibuat setelah router
        // benar-benar berhasil.
        Queue::assertNotPushed(SendWhatsappNotification::class);

        // 2. Job suspend dijalankan sungguhan lewat queue.
        $this->runPushedJob(SuspendCustomer::class);

        $this->inTenant($this->tenant, function () use ($customer): void {
            $customer->refresh();

            $this->assertSame(CustomerStatus::Ditangguhkan, $customer->status);

            $log = SuspendLog::query()->sole();
            $this->assertSame(SuspendLogStatus::Succeeded, $log->status);
            $this->assertSame(SuspendAction::Suspend, $log->action);

            $notification = WhatsappNotification::query()->sole();
            $this->assertSame(WhatsappStatus::Queued, $notification->status);
            $this->assertSame('6281298765432', $notification->to_number);
            $this->assertSame($log->getKey(), $notification->suspend_log_id);
            $this->assertStringContainsString('Rp 275.000', $notification->message);
        });

        // 3. Job notifikasi dijalankan. Token belum diisi, jadi di-skip
        //    (bukan failed) supaya tidak di-retry tanpa akhir.
        Queue::assertPushed(SendWhatsappNotification::class, 1);

        $this->runPushedJob(SendWhatsappNotification::class);

        $this->inTenant($this->tenant, function (): void {
            $notification = WhatsappNotification::query()->sole();

            $this->assertSame(WhatsappStatus::Skipped, $notification->status);
            $this->assertSame(1, $notification->attempts);
        });
    }

    public function test_a_second_daily_run_does_not_suspend_the_same_customer_again(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeSuspendableCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'due_date' => now()->subDays(20)->toDateString(),
            ]);
        });

        Queue::fake();

        $runner = app(OverdueScanRunner::class);

        (new ScanOverdueInvoices)->handle($runner);
        $first = $runner->run();

        $this->assertSame(1, $first->suspended);

        // Jalankan lagi di hari berikutnya dengan status yang sudah berubah.
        $this->inTenant($this->tenant, function (): void {
            Pelanggan::query()->sole()->forceFill([
                'status' => CustomerStatus::Ditangguhkan,
            ])->save();
        });

        $second = $runner->run();

        $this->assertSame(0, $second->suspended, 'Pelanggan yang sudah suspended tidak boleh diantrekan lagi.');
    }

    public function test_invoice_data_is_readable_after_the_suspend_so_a_reaudit_is_possible(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeSuspendableCustomer($this->tenant);

            $invoice = $this->makeInvoice($customer, [
                'invoice_number' => 'INV-AUDIT',
                'due_date' => now()->subDays(10)->toDateString(),
            ]);

            app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Overdue,
                tagihan: $invoice,
            );
        });

        $this->inTenant($this->tenant, function (): void {
            $log = SuspendLog::query()->sole();

            // Audit harus bisa ditelusuri balik ke tagihan asalnya.
            $invoice = Tagihan::query()->sole();
            $this->assertSame($invoice->getKey(), $log->tagihan_id);
            $this->assertSame('INV-AUDIT', $invoice->nomor_tagihan);

            $customer = Pelanggan::query()->sole();
            $this->assertCount(1, $customer->suspendLogs);
            $this->assertCount(1, $customer->tagihan);
        });
    }

    /**
     * Menjalankan satu job yang tertangkap Queue::fake(), seperti yang akan
     * dilakukan worker queue. Job dijalankan tanpa konteks tenant, supaya
     * serialisasi dan pembawaan tenant ikut teruji.
     *
     * @param  class-string  $jobClass
     */
    private function runPushedJob(string $jobClass): void
    {
        $pushed = collect(Queue::pushed($jobClass));

        $this->assertCount(1, $pushed, "Harus ada tepat satu {$jobClass} yang diantrekan.");

        $job = $pushed->first();

        $this->assertInstanceOf($jobClass, $job);

        app(TenantContext::class)->forget();

        $job->handle(app(TenantContext::class));
    }
}
