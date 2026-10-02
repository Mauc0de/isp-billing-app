<?php

namespace App\Enums;

/**
 * Status pelanggan.
 *
 * Nilainya harus sama persis dengan yang disimpan di kolom pelanggans.status
 * dan yang diperiksa view. Sebelumnya enum ini memakai nilai Inggris
 * (active/suspended/terminated) sementara kolomnya memakai 'aktif', sehingga
 * casting ke enum akan melempar ValueError.
 */
enum CustomerStatus: string
{
    case Aktif = 'aktif';
    case Ditangguhkan = 'ditangguhkan';
    case Menunggak = 'menunggak';
    case Berhenti = 'berhenti';

    public function isActive(): bool
    {
        return $this === self::Aktif;
    }

    public function isSuspended(): bool
    {
        return $this === self::Ditangguhkan;
    }
}
