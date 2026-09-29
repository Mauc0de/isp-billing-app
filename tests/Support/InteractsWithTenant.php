<?php

namespace Tests\Support;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Models\Pelanggan;
use App\Models\Router;
use App\Models\Tagihan;
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
    protected function makePelanggan(Tenant $tenant, array $attributes = []): Pelanggan
    {
        return $this->inTenant($tenant, fn (): Pelanggan => Pelanggan::query()->create(array_merge([
            'nama' => 'Budi Santoso',
            'telepon' => '081234567890',
            'email' => 'budi@example.test',
            'status' => CustomerStatus::Aktif->value,
        ], $attributes)));
    }

    /**
     * Pelanggan yang sudah terhubung ke router, sehingga siap disuspend.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function makeSuspendablePelanggan(Tenant $tenant, array $attributes = []): Pelanggan
    {
        $router = $this->makeRouter($tenant);

        return $this->makePelanggan($tenant, array_merge([
            'router_id' => $router->getKey(),
            'mikrotik_username' => 'ppp-0001',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function makeTagihan(Pelanggan $pelanggan, array $attributes = []): Tagihan
    {
        return $this->inTenant(
            Tenant::query()->findOrFail($pelanggan->tenant_id),
            fn (): Tagihan => Tagihan::query()->create(array_merge([
                'pelanggan_id' => $pelanggan->getKey(),
                'nomor_tagihan' => 'INV-2026-0001',
                'status' => InvoiceStatus::BelumBayar->value,
                'jumlah' => 150000,
                'tanggal_terbit' => now()->startOfMonth()->toDateString(),
                'jatuh_tempo' => now()->addDays(7)->toDateString(),
            ], $attributes)),
        );
    }
}
