<?php

namespace Tests\Feature\Jobs;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Enums\RouterStatus;
use App\Enums\WhatsappProvider;
use App\Enums\WhatsappStatus;
use App\Jobs\SendWhatsappNotification;
use App\Mikrotik\RouterClientFactory;
use App\Models\Customer;
use App\Models\Router;
use App\Models\Tenant;
use App\Models\WhatsappNotification;
use App\Settings\TenantSettings;
use App\Suspension\OverdueInvoiceScanner;
use App\Suspension\RouterStatusMonitor;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakeRouterClient;
use Tests\Support\FakeRouterClientFactory;
use Tests\Support\InteractsWithTenant;
use Tests\TestCase;

class AutomationJobsTest extends TestCase
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

    public function test_a_job_carries_its_own_tenant_context(): void
    {
        $other = Tenant::factory()->create();

        $this->inTenant($this->tenant, function (): void {
            $this->makeCustomer($this->tenant, ['customer_number' => 'OWN-TENANT']);
        });

        // Job dijalankan tanpa konteks tenant sama sekali, seperti kondisi
        // worker queue yang sebenarnya.
        app(TenantContext::class)->forget();

        $notificationId = $this->inTenant($this->tenant, function (): string {
            $customer = Customer::query()->where('customer_number', 'OWN-TENANT')->firstOrFail();

            return (string) WhatsappNotification::query()->create([
                'customer_id' => $customer->getKey(),
                'to_number' => '6281234567890',
                'message' => 'Halo',
                'status' => WhatsappStatus::Queued,
            ])->getKey();
        });

        app(TenantContext::class)->forget();

        $this->runNotificationJob($notificationId);

        $this->inTenant($this->tenant, function (): void {
            $notification = WhatsappNotification::query()->sole();

            $this->assertSame(WhatsappStatus::Skipped, $notification->status);
            $this->assertSame(1, $notification->attempts);
        });

        $this->assertNotSame(
            (string) $this->tenant->getKey(),
            (string) $other->getKey(),
        );
    }

    public function test_notification_job_is_idempotent_for_non_queued_rows(): void
    {
        Http::preventStrayRequests();

        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            WhatsappNotification::query()->create([
                'customer_id' => $customer->getKey(),
                'to_number' => '6281234567890',
                'message' => 'Halo',
                'status' => WhatsappStatus::Sent,
                'attempts' => 1,
            ]);
        });

        $id = $this->inTenant($this->tenant, function (): string {
            $notification = WhatsappNotification::query()->sole();

            return (string) $notification->getKey();
        });

        // Kalau job salah jalan pada notifikasi yang sudah terkirim, HTTP fake
        // akan menangkap request dan test ini gagal di assertNothingSent().
        $this->runNotificationJob($id);

        $this->inTenant($this->tenant, function (): void {
            $notification = WhatsappNotification::query()->sole();

            $this->assertSame(WhatsappStatus::Sent, $notification->status);
            $this->assertSame(1, $notification->attempts);
        });

        Http::assertNothingSent();
    }

    public function test_notification_job_marks_skipped_when_provider_is_disabled(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            WhatsappNotification::query()->create([
                'customer_id' => $customer->getKey(),
                'to_number' => '6281234567890',
                'message' => 'Halo',
                'status' => WhatsappStatus::Queued,
            ]);
        });

        $id = $this->inTenant($this->tenant, function (): string {
            return (string) WhatsappNotification::query()->sole()->getKey();
        });

        $this->runNotificationJob($id);

        $this->inTenant($this->tenant, function (): void {
            $notification = WhatsappNotification::query()->sole();

            $this->assertSame(WhatsappStatus::Skipped, $notification->status);
            $this->assertStringContainsString('belum dikonfigurasi', (string) $notification->error);
            $this->assertNull($notification->sent_at);
        });
    }

    public function test_router_monitor_records_online_status(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $router = $this->makeRouter($this->tenant);

            $status = app(RouterStatusMonitor::class)->refresh($router);

            $this->assertSame(RouterStatus::Online, $status);
            $this->assertNotNull($router->refresh()->last_seen_at);
            $this->assertNull($router->last_error);
        });
    }

    public function test_router_monitor_records_offline_status_with_the_error(): void
    {
        $this->router->reachable = false;

        $this->inTenant($this->tenant, function (): void {
            $router = $this->makeRouter($this->tenant);

            $status = app(RouterStatusMonitor::class)->refresh($router);

            $this->assertSame(RouterStatus::Offline, $status);
            $this->assertStringContainsString('Connection refused', (string) $router->refresh()->last_error);
            $this->assertNull($router->last_seen_at);
        });
    }

    public function test_long_offline_router_is_deactivated(): void
    {
        $this->router->reachable = false;

        $this->inTenant($this->tenant, function (): void {
            $router = $this->makeRouter($this->tenant, [
                'last_seen_at' => now()->subDays(30),
            ]);

            app(RouterStatusMonitor::class)->refresh($router);

            $this->assertFalse($router->refresh()->is_active);
        });
    }

    public function test_recently_seen_router_stays_active_even_when_unreachable(): void
    {
        $this->router->reachable = false;

        $this->inTenant($this->tenant, function (): void {
            $router = $this->makeRouter($this->tenant, [
                'last_seen_at' => now()->subDay(),
            ]);

            app(RouterStatusMonitor::class)->refresh($router);

            $this->assertTrue($router->refresh()->is_active);
        });
    }

    public function test_settings_never_leak_between_tenants(): void
    {
        $other = Tenant::factory()->create();

        $this->inTenant($this->tenant, function (): void {
            app(TenantSettings::class)->put(TenantSettings::GRACE_PERIOD_DAYS, 7, 'integer');

            $this->assertSame(7, app(TenantSettings::class)->gracePeriodDays());
        });

        $this->inTenant($other, function (): void {
            // Tenant lain harus melihat default, bukan nilai milik tenant pertama.
            $this->assertSame(3, app(TenantSettings::class)->gracePeriodDays());
        });

        $this->inTenant($this->tenant, function (): void {
            $this->assertSame(7, app(TenantSettings::class)->gracePeriodDays());
        });
    }

    public function test_settings_fall_back_to_config_when_tenant_has_none(): void
    {
        config()->set('whatsapp.default_provider', 'fonnte');

        $this->inTenant($this->tenant, function (): void {
            $this->assertSame(
                WhatsappProvider::Fonnte,
                app(TenantSettings::class)->whatsappProvider(),
            );
        });

        $this->inTenant($this->tenant, function (): void {
            app(TenantSettings::class)->put(
                TenantSettings::WHATSAPP_PROVIDER,
                'wablas',
                'string',
            );

            $this->assertSame(
                WhatsappProvider::Wablas,
                app(TenantSettings::class)->whatsappProvider(),
            );
        });
    }

    public function test_unknown_provider_name_falls_back_to_disabled(): void
    {
        $this->inTenant($this->tenant, function (): void {
            app(TenantSettings::class)->put(
                TenantSettings::WHATSAPP_PROVIDER,
                'entah',
                'string',
            );

            $this->assertSame(
                WhatsappProvider::Disabled,
                app(TenantSettings::class)->whatsappProvider(),
            );
        });
    }

    public function test_custom_template_overrides_the_default(): void
    {
        $this->inTenant($this->tenant, function (): void {
            app(TenantSettings::class)->put(
                'whatsapp.templates.suspend',
                'Halo {customer}, mohon bayar {amount}.',
                'string',
            );

            $this->assertSame(
                'Halo {customer}, mohon bayar {amount}.',
                app(TenantSettings::class)->template('suspend'),
            );
        });
    }

    public function test_customer_phone_number_is_normalized_to_international_format(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $cases = [
                '081234567890' => '6281234567890',
                '0812 3456 7890' => '6281234567890',
                '+62 812-3456-7890' => '6281234567890',
                '6281234567890' => '6281234567890',
            ];

            foreach ($cases as $input => $expected) {
                $this->assertSame(
                    $expected,
                    Customer::normalizePhoneNumber($input),
                    "Gagal menormalisasi {$input}",
                );
            }

            $this->assertNull(Customer::normalizePhoneNumber(null));
            $this->assertNull(Customer::normalizePhoneNumber('-'));
        });
    }

    public function test_whatsapp_number_is_preferred_over_phone(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant, [
                'phone' => '0811111111',
                'whatsapp_number' => '0822222222',
            ]);

            $this->assertSame('62822222222', $customer->whatsappTarget());
        });
    }

    public function test_phone_is_used_when_whatsapp_number_is_absent(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant, [
                'phone' => '0811111111',
                'whatsapp_number' => null,
            ]);

            $this->assertSame('62811111111', $customer->whatsappTarget());
        });
    }

    public function test_scanner_ignores_invoices_of_another_tenant(): void
    {
        $other = Tenant::factory()->create();

        $this->inTenant($other, function () use ($other): void {
            $customer = $this->makeCustomer($other, ['customer_number' => 'OTHER-TENANT']);

            $this->makeInvoice($customer, [
                'invoice_number' => 'OTHER-INV',
                'status' => InvoiceStatus::Unpaid,
                'due_date' => now()->subDays(60)->toDateString(),
            ]);
        });

        $ids = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findSuspendableCustomerIds(),
        );

        $this->assertSame([], $ids);
    }

    public function test_router_relation_is_tenant_scoped(): void
    {
        $other = Tenant::factory()->create();

        $this->inTenant($other, function () use ($other): void {
            $this->makeRouter($other, ['name' => 'Router milik tenant lain']);
        });

        $this->inTenant($this->tenant, function (): void {
            $this->assertSame(0, Router::query()->count());
        });
    }

    public function test_customer_status_enum_still_round_trips_after_migration(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant, [
                'status' => CustomerStatus::Suspended,
            ]);

            $this->assertSame(CustomerStatus::Suspended, $customer->refresh()->status);
        });
    }

    /**
     * Menjalankan job tanpa konteks tenant, seperti kondisi worker queue.
     */
    private function runNotificationJob(string $notificationId): void
    {
        app(TenantContext::class)->forget();

        (new SendWhatsappNotification((string) $this->tenant->getKey(), $notificationId))
            ->handle(app(TenantContext::class));
    }
}
