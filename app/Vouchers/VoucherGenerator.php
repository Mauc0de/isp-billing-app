<?php

namespace App\Vouchers;

use App\Enums\VoucherStatus;
use App\Models\Paket;
use App\Models\Voucher;
use App\Models\VoucherBatch;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Pembuat kode voucher massal.
 *
 * Kode dibuat dari alfabet tanpa karakter ambigu (0/O, 1/I/L) supaya mudah
 * dibaca pelanggan saat mengetik. Entropi dijaga dengan panjang kode minimal
 * 6 karakter. Pengecekan unik dilakukan per kode sebelum insert; seluruh
 * batch dibungkus satu transaksi agar tidak ada batch setengah jadi.
 */
class VoucherGenerator
{
    /**
     * Alfabet tanpa karakter yang mudah tertukar.
     */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function __construct(private readonly TenantContext $tenantContext) {}

    /**
     * @return VoucherBatch batch beserta voucher-voucher yang baru dibuat
     */
    public function generate(
        string $nama,
        ?Paket $paket,
        int $jumlah,
        int $masaAktifHari = 30,
        ?string $prefix = null,
        int $panjangKode = 8,
        ?string $catatan = null,
    ): VoucherBatch {
        $jumlah = max(1, min($jumlah, 1000));
        $panjangKode = max(6, min($panjangKode, 24));

        return DB::transaction(function () use ($nama, $paket, $jumlah, $masaAktifHari, $prefix, $panjangKode, $catatan): VoucherBatch {
            $batch = VoucherBatch::query()->create([
                'tenant_id' => $this->tenantContext->id(),
                'paket_id' => $paket?->getKey(),
                'nama' => $nama,
                'kode_prefix' => $prefix,
                'jumlah' => $jumlah,
                'panjang_kode' => $panjangKode,
                'masa_aktif_hari' => $masaAktifHari,
                'catatan' => $catatan,
            ]);

            $harga = (int) ($paket?->harga ?? 0);
            $kodeTerpakai = [];

            for ($i = 0; $i < $jumlah; $i++) {
                $kode = $this->uniqueKode($prefix, $panjangKode, $kodeTerpakai);
                $kodeTerpakai[$kode] = true;

                Voucher::query()->create([
                    'tenant_id' => $batch->tenant_id,
                    'batch_id' => $batch->getKey(),
                    'paket_id' => $paket?->getKey(),
                    'kode' => $kode,
                    'harga' => $harga,
                    'masa_aktif_hari' => $masaAktifHari,
                    'status' => VoucherStatus::BelumDipakai,
                    'kadaluarsa_at' => now()->addDays($masaAktifHari),
                ]);
            }

            return $batch->load('vouchers');
        });
    }

    /**
     * Kode belum ada di database dan belum dipakai di batch ini.
     *
     * @param  array<string, bool>  $kodeTerpakai
     */
    private function uniqueKode(?string $prefix, int $panjangKode, array $kodeTerpakai): string
    {
        do {
            $kode = ($prefix ?? '').$this->randomCode($panjangKode);
        } while (isset($kodeTerpakai[$kode]) || Voucher::query()->where('kode', $kode)->exists());

        return $kode;
    }

    private function randomCode(int $length): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }
}
