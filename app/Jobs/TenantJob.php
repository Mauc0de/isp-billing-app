<?php

namespace App\Jobs;

use App\Tenancy\TenantContext;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Base job yang membawa tenant-nya sendiri.
 *
 * Job tidak pernah menemukan tenant dari user yang login, karena job dijalankan
 * oleh scheduler/worker yang tidak punya sesi. Karena itu tenant_id ikut
 * diserialisasi dan konteksnya dipasang ulang di handle().
 *
 * Job juga umumnya hanya membawa ID, bukan model Eloquent, supaya model bisa
 * di-resolve ulang di dalam konteks tenant yang benar.
 *
 * Catatan: property job sengaja tidak readonly. Serialisasi queue Laravel
 * memulihkan property lewat ReflectionProperty::setValue(), yang gagal untuk
 * property readonly.
 */
abstract class TenantJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $tenantId) {}

    public function handle(TenantContext $tenantContext): void
    {
        $tenantContext->run($this->tenantId, fn (): mixed => $this->handleInTenant());
    }

    /**
     * Resolve dependency dari container.
     *
     * Dipakai dari handleInTenant() alih-alih injection parameter, karena
     * override tidak boleh menambah parameter wajib dari method abstract.
     *
     * @template T of object
     *
     * @param  class-string<T>|string  $abstract
     * @return T
     */
    protected function resolve(string $abstract): object
    {
        return Container::getInstance()->make($abstract);
    }

    abstract protected function handleInTenant(): void;
}
