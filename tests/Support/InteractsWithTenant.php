<?php

namespace Tests\Support;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Router;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Closure;

/**
 * Helper untuk membuat data uji di dalam konteks tenant.
 *
 * Semua model baru memakai global scope tenant, jadi setiap operasi harus
 * dibungkus inTenant() supaya query-nya tidak mengembalikan nol baris.
 */
trait InteractsWithTenant
{
    protected function inTenant(Tenant $tenant, Closure $callback): mixed
    {
        return app(TenantContext::class)->run((string) $tenant->getKey(), $callback);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeRouter(Tenant $tenant, array $attributes = []): Router
    {
        return $this->inTenant($tenant, fn (): Router => Router::factory()
            ->forTenant($tenant)
            ->create($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeCustomer(Tenant $tenant, array $attributes = []): Customer
    {
        return $this->inTenant($tenant, fn (): Customer => Customer::query()->create(array_merge([
            'customer_number' => 'CUSTOMER-001',
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'whatsapp_number' => '0812 3456 7890',
            'status' => CustomerStatus::Active,
        ], $attributes)));
    }

    /**
     * Pelanggan yang sudah terhubung ke router, sehingga siap disuspend.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function makeSuspendableCustomer(Tenant $tenant, array $attributes = []): Customer
    {
        $router = $this->makeRouter($tenant);

        return $this->makeCustomer($tenant, array_merge([
            'router_id' => $router->getKey(),
            'mikrotik_username' => 'ppp-0001',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeInvoice(Customer $customer, array $attributes = []): Invoice
    {
        return Invoice::query()->create(array_merge([
            'customer_id' => $customer->getKey(),
            'invoice_number' => 'INV-2026-0001',
            'status' => InvoiceStatus::Unpaid,
            'package_name' => 'Paket 10 Mbps',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'amount' => 150000,
            'discount' => 0,
            'total' => 150000,
            'generated_at' => now(),
        ], $attributes));
    }
}
