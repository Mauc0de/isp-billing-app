<?php

namespace App\Contracts;

use App\Enums\RouterSuspendMethod;
use App\Exceptions\RouterOperationFailed;
use App\Mikrotik\RouterHealth;

/**
 * Abstraksi akses ke RouterOS.
 *
 * Implementasi sungguhan ada di App\Mikrotik\RouterOsClient, sedangkan test
 * memakai App\Mikrotik\FakeRouterClient. Kode domain tidak pernah menyentuh
 * library RouterOS secara langsung, sehingga integrasi di masa depan bisa
 * diganti tanpa menyentuh logika suspend/reaktivasi.
 */
interface RouterClient
{
    /**
     * Cek apakah router bisa dihubungi dan ambil identitasnya.
     *
     * Tidak melempar exception saat router mati — status offline dikembalikan
     * sebagai nilai, karena kegagalan koneksi adalah kondisi normal saat
     * memindai banyak router sekaligus.
     */
    public function health(): RouterHealth;

    /**
     * Cabut akses pelanggan sesuai metode yang diminta.
     *
     * @param  string  $username  Kredensial PPP secret di router.
     * @param  string|null  $address  IP Address pelanggan, wajib untuk AddressList.
     *
     * @throws RouterOperationFailed
     */
    public function suspend(RouterSuspendMethod $method, string $username, ?string $address = null): void;

    /**
     * Kembalikan akses pelanggan sesuai kebalikan dari suspend().
     *
     * @throws RouterOperationFailed
     */
    public function reactivate(RouterSuspendMethod $method, string $username, ?string $address = null): void;

    /**
     * Jumlah sesi PPP aktif di router, untuk monitoring.
     *
     * @throws RouterOperationFailed
     */
    public function activeSessionCount(): int;
}
