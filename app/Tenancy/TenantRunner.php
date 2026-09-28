<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Closure;

/**
 * Menjalankan sesuatu untuk setiap tenant aktif.
 *
 * Scheduler dan worker tidak punya sesi user, jadi tidak ada tenant context.
 * Pemindaian tagihan harus dijalankan satu tenant satu tenant supaya global
 * scope tidak membuat semua query mengembalikan nol baris.
 */
class TenantRunner
{
    public function __construct(private readonly TenantContext $context) {}

    public function forEachActiveTenant(Closure $callback): void
    {
        Tenant::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->each(fn (Tenant $tenant) => $this->forTenant($tenant, $callback));
    }

    /**
     * Menjalankan sesuatu untuk satu tenant tertentu, aktif atau tidak.
     *
     * Dipakai command yang menerima --tenant, supaya operator bisa memperbaiki
     * satu tenant tanpa ikut mengubah tenant lain.
     */
    public function forTenant(Tenant $tenant, Closure $callback): void
    {
        $this->context->run(
            (string) $tenant->getKey(),
            fn (): mixed => $callback($tenant),
        );
    }
}
