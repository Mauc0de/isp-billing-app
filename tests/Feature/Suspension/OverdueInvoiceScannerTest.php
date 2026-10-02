<?php

namespace Tests\Feature\Suspension;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Models\Pelanggan;
use App\Models\Tenant;
use App\Settings\TenantSettings;
use App\Suspension\OverdueInvoiceScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithTenant;
use Tests\TestCase;

class OverdueInvoiceScannerTest extends TestCase
{
    use InteractsWithTenant;
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
    }

    public function test_only_invoices_past_the_grace_period_are_suspendable(): void
    {
        // Masa tenggang default 3 hari.
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'BELUM-JATUH-TEMPO',
                'due_date' => now()->addDay()->toDateString(),
            ]);
            $this->makeInvoice($customer, [
                'invoice_number' => 'MASIH-TENGGANG',
                'due_date' => now()->subDays(2)->toDateString(),
            ]);
            $this->makeInvoice($customer, [
                'invoice_number' => 'LEWAT-TENGGANG',
                'due_date' => now()->subDays(5)->toDateString(),
            ]);
        });

        $ids = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findSuspendableCustomerIds(),
        );

        $this->assertCount(1, $ids);
    }

    public function test_a_customer_is_returned_once_even_with_many_overdue_invoices(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'INV-A',
                'due_date' => now()->subDays(5)->toDateString(),
            ]);
            $this->makeInvoice($customer, [
                'invoice_number' => 'INV-B',
                'due_date' => now()->subDays(9)->toDateString(),
            ]);
        });

        $ids = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findSuspendableCustomerIds(),
        );

        $this->assertCount(1, $ids);
    }

    public function test_paid_invoices_are_never_suspendable(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'LUNAS',
                'status' => InvoiceStatus::Lunas,
                'due_date' => now()->subDays(30)->toDateString(),
            ]);
        });

        $ids = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findSuspendableCustomerIds(),
        );

        $this->assertSame([], $ids);
    }

    public function test_already_suspended_customers_are_not_suspendable_again(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant, [
                'status' => CustomerStatus::Ditangguhkan,
            ]);

            $this->makeInvoice($customer, [
                'due_date' => now()->subDays(30)->toDateString(),
            ]);
        });

        $ids = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findSuspendableCustomerIds(),
        );

        $this->assertSame([], $ids);
    }

    public function test_grace_period_is_read_from_tenant_settings(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'LEWAT-7-HARI',
                'due_date' => now()->subDays(7)->toDateString(),
            ]);
        });

        // Masa tenggang default 3 hari, jadi tagihan 7 hari lalu layak disuspend.
        $withDefault = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findSuspendableCustomerIds(),
        );

        $this->assertCount(1, $withDefault);

        // Setelah masa tenggang dinaikkan ke 10 hari, tagihan yang sama
        // seharusnya belum layak disuspend.
        $withLongerGrace = $this->inTenant($this->tenant, function (): array {
            $settings = app(TenantSettings::class);
            $settings->put(TenantSettings::GRACE_PERIOD_DAYS, 10, 'integer');

            return app(OverdueInvoiceScanner::class)->findSuspendableCustomerIds();
        });

        $this->assertSame([], $withLongerGrace);
    }

    public function test_due_reminders_cover_invoices_due_within_the_reminder_window(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'invoice_number' => 'JATUH-TEMPO-BESOK',
                'due_date' => now()->addDay()->toDateString(),
            ]);
            $this->makeInvoice($customer, [
                'invoice_number' => 'JATUH-TEMPO-JAUH',
                'due_date' => now()->addDays(20)->toDateString(),
            ]);
        });

        $ids = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findDueReminderInvoiceIds(),
        );

        $this->assertCount(1, $ids);
    }

    public function test_due_reminders_are_not_repeated_on_the_same_day(): void
    {
        $this->inTenant($this->tenant, function (): void {
            $customer = $this->makeCustomer($this->tenant);

            $this->makeInvoice($customer, [
                'due_date' => now()->addDay()->toDateString(),
            ]);
        });

        $first = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findDueReminderInvoiceIds(),
        );

        $this->assertCount(1, $first);

        // Simulasikan pengingat yang sudah dikirim hari ini.
        $this->inTenant($this->tenant, function (): void {
            $customer = Pelanggan::query()->firstOrFail();
            $invoice = $customer->tagihan()->firstOrFail();

            $invoice->whatsappNotifications()->create([
                'to_number' => '6281234567890',
                'message' => 'Pengingat',
                'status' => 'sent',
            ]);
        });

        $second = $this->inTenant(
            $this->tenant,
            fn (): array => app(OverdueInvoiceScanner::class)->findDueReminderInvoiceIds(),
        );

        $this->assertSame([], $second);
    }
}
