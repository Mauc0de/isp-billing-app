<?php

namespace Tests\Feature\Suspension;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Enums\RouterSuspendMethod;
use App\Enums\SuspendAction;
use App\Enums\SuspendLogStatus;
use App\Enums\SuspensionSource;
use App\Enums\WhatsappStatus;
use App\Exceptions\RouterOperationFailed;
use App\Jobs\SendWhatsappNotification;
use App\Mikrotik\RouterClientFactory;
use App\Models\Pelanggan;
use App\Models\Router;
use App\Models\SuspendLog;
use App\Models\Tagihan;
use App\Models\Tenant;
use App\Models\WhatsappNotification;
use App\Suspension\CustomerSuspender;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Support\FakeRouterClient;
use Tests\Support\FakeRouterClientFactory;
use Tests\TestCase;

class CustomerSuspenderTest extends TestCase
{
    use RefreshDatabase;

    private FakeRouterClient $router;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new FakeRouterClient;

        $this->app->bind(
            RouterClientFactory::class,
            fn (): FakeRouterClientFactory => new FakeRouterClientFactory($this->router),
        );

        $this->tenant = Tenant::factory()->create(['name' => 'ISP Maju']);
    }

    public function test_suspend_updates_customer_and_writes_audit_log(): void
    {
        $this->inTenant(function (): void {
            $customer = $this->makeCustomer();
            $router = $customer->router;

            $log = app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Overdue,
                reason: 'Tagihan lewat jatuh tempo.',
            );

            $this->assertSame(SuspendLogStatus::Succeeded, $log->status);
            $this->assertSame(SuspendAction::Suspend, $log->action);
            $this->assertSame(SuspensionSource::Overdue, $log->source);
            $this->assertSame(RouterSuspendMethod::PppSecret, $log->method);
            $this->assertSame($router->getKey(), $log->router_id);

            $customer->refresh();
            $this->assertSame(CustomerStatus::Ditangguhkan, $customer->status);
            $this->assertSame(SuspensionSource::Overdue, $customer->suspension_source);
            $this->assertNotNull($customer->suspended_at);

            $this->assertCount(1, $this->router->callsFor('suspend'));
            $this->assertSame('ppp-0001', $this->router->callsFor('suspend')[0]['username']);
        });
    }

    public function test_suspend_queues_a_whatsapp_notification_with_the_invoice_details(): void
    {
        Queue::fake([SendWhatsappNotification::class]);

        $this->inTenant(function (): void {
            $customer = $this->makeCustomer();
            $invoice = $this->makeInvoice($customer, ['total' => 150000]);

            app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Overdue,
                tagihan: $invoice,
            );

            $notification = WhatsappNotification::query()->sole();

            $this->assertSame(WhatsappStatus::Queued, $notification->status);
            $this->assertSame('6281234567890', $notification->to_number);
            $this->assertStringContainsString('ISP Maju', $notification->message);
            $this->assertStringContainsString('Rp 150.000', $notification->message);
        });
    }

    public function test_failed_router_operation_keeps_customer_active_and_logs_the_error(): void
    {
        $this->router->failEverything = RouterOperationFailed::connectionFailed(
            'Router Pusat', '10.0.0.1', 8728,
        );

        $this->inTenant(function (): void {
            $customer = $this->makeCustomer();

            $log = app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Overdue,
            );

            $this->assertSame(SuspendLogStatus::Failed, $log->status);
            $this->assertStringContainsString('Tidak bisa menghubungi', (string) $log->error);

            // Status harus tetap Active supaya pemindaian berikutnya mencoba lagi.
            $this->assertSame(CustomerStatus::Aktif, $customer->refresh()->status);
        });
    }

    public function test_customer_without_router_link_is_skipped(): void
    {
        $this->inTenant(function (): void {
            $customer = Pelanggan::query()->create([
                'customer_number' => 'CUSTOMER-X',
                'nama' => 'Tanpa Router',
                'status' => CustomerStatus::Aktif,
            ]);

            $log = app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Manual,
            );

            $this->assertSame(SuspendLogStatus::Skipped, $log->status);
            $this->assertStringContainsString('belum ditautkan ke router', (string) $log->reason);
            $this->assertSame([], $this->router->calls);
        });
    }

    public function test_suspending_an_already_suspended_customer_is_skipped(): void
    {
        $this->inTenant(function (): void {
            $customer = $this->makeCustomer();

            app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Overdue,
            );

            $second = app(CustomerSuspender::class)->suspend(
                pelanggan: $customer->refresh(),
                source: SuspensionSource::Overdue,
            );

            $this->assertSame(SuspendLogStatus::Skipped, $second->status);
            $this->assertCount(1, $this->router->callsFor('suspend'));
            $this->assertSame(2, SuspendLog::query()->count());
        });
    }

    public function test_address_list_falls_back_to_disabling_ppp_secret(): void
    {
        // Hanya address-list yang gagal, supaya fallback ke ppp_secret terlihat.
        $this->router->failOn(
            RouterSuspendMethod::AddressList,
            RouterOperationFailed::addressUnavailable('Router Pusat', 'ppp-0001'),
        );

        $this->inTenant(function (): void {
            $router = Router::factory()
                ->forTenant($this->tenant)
                ->usingAddressList()
                ->create();

            $customer = Pelanggan::query()->create([
                'router_id' => $router->getKey(),
                'mikrotik_username' => 'ppp-0001',
                'customer_number' => 'CUSTOMER-ADDR',
                'nama' => 'Pelanggan Address List',
                'status' => CustomerStatus::Aktif,
            ]);

            $log = app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Overdue,
            );

            $this->assertSame(SuspendLogStatus::Succeeded, $log->status);
            $this->assertSame(RouterSuspendMethod::PppSecret, $log->method);
            $this->assertSame('address_list', $log->context['configured_method']);

            // Dua percobaan: address-list gagal, lalu fallback ke ppp_secret.
            $this->assertCount(2, $this->router->callsFor('suspend'));
            $this->assertSame('address_list', $this->router->calls[0]['method']);
            $this->assertSame('ppp_secret', $this->router->calls[1]['method']);

            $this->assertSame(CustomerStatus::Ditangguhkan, $customer->refresh()->status);
        });
    }

    public function test_reactivate_restores_customer_and_clears_suspension_fields(): void
    {
        $this->inTenant(function (): void {
            $customer = $this->makeCustomer();

            app(CustomerSuspender::class)->suspend(
                pelanggan: $customer,
                source: SuspensionSource::Overdue,
            );

            $log = app(CustomerSuspender::class)->reactivate($customer->refresh());

            $this->assertSame(SuspendLogStatus::Succeeded, $log->status);
            $this->assertSame(SuspendAction::Reactivate, $log->action);

            $customer->refresh();
            $this->assertSame(CustomerStatus::Aktif, $customer->status);
            $this->assertNull($customer->suspension_source);
            $this->assertNull($customer->suspended_at);
            $this->assertNull($customer->status_reason);
        });
    }

    public function test_reactivating_an_active_customer_is_skipped(): void
    {
        $this->inTenant(function (): void {
            $customer = $this->makeCustomer();

            $log = app(CustomerSuspender::class)->reactivate($customer);

            $this->assertSame(SuspendLogStatus::Skipped, $log->status);
            $this->assertSame([], $this->router->calls);
        });
    }

    private function makeCustomer(): Pelanggan
    {
        $router = Router::factory()->forTenant($this->tenant)->create();

        return Pelanggan::query()->create([
            'router_id' => $router->getKey(),
            'mikrotik_username' => 'ppp-0001',
            'customer_number' => 'CUSTOMER-001',
            'nama' => 'Budi Santoso',
            'telepon' => '081234567890',
            'whatsapp_number' => '0812 3456 7890',
            'status' => CustomerStatus::Aktif,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeInvoice(Pelanggan $pelanggan, array $attributes = []): Tagihan
    {
        return Tagihan::query()->create(array_merge([
            'pelanggan_id' => $pelanggan->getKey(),
            'nomor_tagihan' => 'INV-2026-0001',
            'status' => InvoiceStatus::BelumBayar,
            'jumlah' => 150000,
            'tanggal_terbit' => now()->startOfMonth()->toDateString(),
            'jatuh_tempo' => now()->subDays(5)->toDateString(),
        ], $attributes));
    }

    private function inTenant(Closure $callback): mixed
    {
        return app(TenantContext::class)->run(
            (string) $this->tenant->getKey(),
            $callback,
        );
    }
}
