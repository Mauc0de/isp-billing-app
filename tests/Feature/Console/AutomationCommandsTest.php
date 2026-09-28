<?php

namespace Tests\Feature\Console;

use App\Enums\InvoiceStatus;
use App\Enums\RouterStatus;
use App\Jobs\SuspendCustomer;
use App\Mikrotik\RouterClientFactory;
use App\Models\Tenant;
use App\Suspension\OverdueScanRunner;
use App\Suspension\RouterStatusSyncer;
use App\Suspension\ScanReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Support\FakeRouterClient;
use Tests\Support\FakeRouterClientFactory;
use Tests\Support\InteractsWithTenant;
use Tests\TestCase;

class AutomationCommandsTest extends TestCase
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

        $this->tenant = Tenant::factory()->create();
    }

    public function test_scan_report_is_empty_when_there_is_nothing_to_do(): void
    {
        $report = $this->inTenant(
            $this->tenant,
            fn (): ScanReport => app(OverdueScanRunner::class)->run(),
        );

        $this->assertSame(1, $report->tenants);
        $this->assertSame(0, $report->suspended);
        $this->assertSame(0, $report->reminded);
        $this->assertTrue($report->foundNothing());
    }

    public function test_scan_report_counts_real_work_not_flags(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $overdue = $this->makeCustomer($this->tenant, ['customer_number' => 'OVERDUE']);
            $dueSoon = $this->makeCustomer($this->tenant, ['customer_number' => 'DUE-SOON']);

            $this->makeInvoice($overdue, [
                'invoice_number' => 'INV-OVERDUE',
                'status' => InvoiceStatus::Unpaid,
                'due_date' => now()->subDays(30)->toDateString(),
            ]);
            $this->makeInvoice($dueSoon, [
                'invoice_number' => 'INV-DUE-SOON',
                'status' => InvoiceStatus::Unpaid,
                'due_date' => now()->addDay()->toDateString(),
            ]);
        });

        $report = $this->inTenant(
            $this->tenant,
            fn (): ScanReport => app(OverdueScanRunner::class)->run(),
        );

        $this->assertSame(1, $report->suspended);
        $this->assertSame(1, $report->reminded);
        $this->assertFalse($report->foundNothing());
    }

    public function test_scan_report_counts_aggregate_across_tenants(): void
    {
        $other = Tenant::factory()->create();

        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'INV-A',
                'due_date' => now()->subDays(30)->toDateString(),
            ]);
        });

        $this->inTenant($other, function () use ($other): void {
            $customer = $this->makeCustomer($other, ['customer_number' => 'OTHER-001']);

            $this->makeInvoice($customer, [
                'invoice_number' => 'INV-B',
                'due_date' => now()->subDays(30)->toDateString(),
            ]);
        });

        $report = app(OverdueScanRunner::class)->run();

        $this->assertSame(2, $report->tenants);
        $this->assertSame(2, $report->suspended);
    }

    public function test_scan_can_be_limited_to_suspensions_only(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'INV-DUE-SOON',
                'due_date' => now()->addDay()->toDateString(),
            ]);
        });

        $report = $this->inTenant(
            $this->tenant,
            fn (): ScanReport => app(OverdueScanRunner::class)->run(remind: false),
        );

        $this->assertSame(0, $report->suspended);
        $this->assertSame(0, $report->reminded);
    }

    public function test_scan_command_reports_zero_instead_of_a_bare_flag(): void
    {
        Queue::fake();

        // Command lama mencetak nilai boolean sebagai angka, jadi selalu
        // melaporkan "suspend: 1" walau tidak ada pelanggan yang ditindak.
        $this->artisan('isp:scan-overdue')
            ->expectsOutputToContain('tidak ada tagihan yang perlu ditindak')
            ->assertSuccessful();
    }

    public function test_scan_command_reports_the_actual_counts(): void
    {
        Queue::fake();

        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'INV-OVERDUE',
                'status' => InvoiceStatus::Unpaid,
                'due_date' => now()->subDays(30)->toDateString(),
            ]);
        });

        $this->artisan('isp:scan-overdue')
            ->expectsOutputToContain('1 pelanggan diantrekan untuk suspend')
            ->assertSuccessful();

        Queue::assertPushed(SuspendCustomer::class, 1);
    }

    public function test_scan_command_rejects_disabling_both_actions(): void
    {
        $this->artisan('isp:scan-overdue --suspend-only --remind-only')
            ->expectsOutputToContain('Pilih minimal salah satu')
            ->assertFailed();
    }

    public function test_router_sync_reports_nothing_to_check(): void
    {
        $this->artisan('isp:sync-routers')
            ->expectsOutputToContain('Tidak ada router aktif')
            ->assertSuccessful();
    }

    public function test_router_sync_counts_online_and_offline_routers(): void
    {
        $this->inTenant($this->tenant, fn () => $this->makeRouter($this->tenant, ['name' => 'Router A']));

        $report = $this->inTenant(
            $this->tenant,
            fn () => app(RouterStatusSyncer::class)->sync(),
        );

        $this->assertSame(1, $report->checked());
        $this->assertSame(1, $report->online());
        $this->assertSame(0, $report->offline());
    }

    public function test_router_sync_command_fails_when_a_router_is_offline(): void
    {
        $this->router->reachable = false;

        $this->inTenant($this->tenant, fn () => $this->makeRouter($this->tenant));

        $this->artisan('isp:sync-routers')
            ->expectsOutputToContain('1 offline')
            ->assertFailed();
    }

    public function test_router_test_command_explains_when_no_router_exists(): void
    {
        $this->artisan('isp:router:test')
            ->expectsOutputToContain('Belum ada router aktif')
            ->assertFailed();
    }

    public function test_router_test_command_reports_an_unknown_router_name(): void
    {
        $this->artisan('isp:router:test "Router Hantu"')
            ->expectsOutputToContain("Router 'Router Hantu' tidak ditemukan")
            ->assertFailed();
    }

    public function test_router_test_command_succeeds_for_an_online_router(): void
    {
        $this->inTenant($this->tenant, fn () => $this->makeRouter($this->tenant, ['name' => 'Router Pusat']));

        $this->artisan('isp:router:test "Router Pusat"')
            ->expectsOutputToContain('Online')
            ->expectsOutputToContain('1 router online')
            ->assertSuccessful();
    }

    public function test_router_test_command_fails_for_an_offline_router(): void
    {
        $this->router->reachable = false;

        $this->inTenant($this->tenant, fn () => $this->makeRouter($this->tenant, ['name' => 'Router Mati']));

        $this->artisan('isp:router:test "Router Mati"')
            ->expectsOutputToContain('Offline')
            ->expectsOutputToContain('1 dari 1 router gagal dites')
            ->assertFailed();
    }

    public function test_router_status_monitor_stores_the_new_status(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $router = $this->makeRouter($this->tenant);

            app(RouterStatusSyncer::class)->sync();

            $this->assertSame(RouterStatus::Online, $router->refresh()->status);
        });
    }
}
