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
     * Alias lama (customer) yang menerjemahkan nama kolom Inggris ke skema
     * penamaan Indonesia, supaya test yang ditulis sebelum rename tetap jalan.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function makeCustomer(Tenant $tenant, array $attributes = []): Pelanggan
    {
        if (array_key_exists('name', $attributes)) {
            $attributes['nama'] = $attributes['name'];
            unset($attributes['name']);
        }

        if (array_key_exists('phone', $attributes)) {
            $attributes['telepon'] = $attributes['phone'];
            unset($attributes['phone']);
        }

        if (($attributes['status'] ?? null) instanceof CustomerStatus) {
            $attributes['status'] = $attributes['status']->value;
        }

        return $this->makePelanggan($tenant, $attributes);
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
    protected function makeSuspendableCustomer(Tenant $tenant, array $attributes = []): Pelanggan
    {
        if (array_key_exists('name', $attributes)) {
            $attributes['nama'] = $attributes['name'];
            unset($attributes['name']);
        }

        if (array_key_exists('phone', $attributes)) {
            $attributes['telepon'] = $attributes['phone'];
            unset($attributes['phone']);
        }

        if (($attributes['status'] ?? null) instanceof CustomerStatus) {
            $attributes['status'] = $attributes['status']->value;
        }

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

    /**
     * Alias lama (invoice) dengan terjemahan ke skema Indonesia.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function makeInvoice(Pelanggan $pelanggan, array $attributes = []): Tagihan
    {
        if (array_key_exists('pelanggan_id', $attributes)) {
            unset($attributes['pelanggan_id']);
        }

        if (array_key_exists('invoice_number', $attributes)) {
            $attributes['nomor_tagihan'] = $attributes['invoice_number'];
            unset($attributes['invoice_number']);
        }

        if (array_key_exists('amount', $attributes)) {
            $attributes['jumlah'] = $attributes['amount'];
            unset($attributes['amount']);
        }

        if (array_key_exists('total', $attributes)) {
            $attributes['jumlah'] = $attributes['total'];
            unset($attributes['total']);
        }

        if (array_key_exists('due_date', $attributes)) {
            $attributes['jatuh_tempo'] = $attributes['due_date'];
            unset($attributes['due_date']);
        }

        if (array_key_exists('generated_at', $attributes)) {
            $attributes['tanggal_terbit'] = $attributes['generated_at'];
            unset($attributes['generated_at']);
        }

        foreach (['package_name', 'period_start', 'period_end', 'discount'] as $legacy) {
            unset($attributes[$legacy]);
        }

        if (($attributes['status'] ?? null) instanceof InvoiceStatus) {
            $attributes['status'] = $attributes['status']->value;
        }

        return $this->makeTagihan($pelanggan, $attributes);
    }
}
