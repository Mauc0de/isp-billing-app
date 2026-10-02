<?php

namespace App\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Tenancy\TenantRunner;
use Illuminate\Support\Facades\DB;

/**
 * Auto-renew: melunasi tagihan pelanggan memakai saldo yang tersimpan.
 *
 * Dipakai dua cara:
 *  - otomatis saat pemindaian tagihan (hanya pelanggan dengan auto_renew), dan
 *  - manual oleh admin dari halaman pelanggan.
 *
 * Aturan: saldo dipotong lewat SaldoLedger (locked), lalu tagihan ditandai
 * lunas dan satu baris pembayaran metode 'saldo' dicatat, semuanya dalam satu
 * transaksi supaya tidak ada tagihan lunas tanpa potongan atau sebaliknya.
 */
class AutoRenew
{
    public function __construct(
        private readonly TenantRunner $tenants,
        private readonly SaldoLedger $ledger,
    ) {}

    /**
     * Melunasi satu tagihan tertentu bila saldo cukup.
     *
     * @return bool true bila berhasil dilunasi
     */
    public function pay(Tagihan $tagihan): bool
    {
        if (! $this->isOutstanding($tagihan)) {
            return false;
        }

        $pelanggan = $this->pelangganOf($tagihan);

        if ($pelanggan === null || ! $this->ledger->hasEnough($pelanggan, $tagihan->jumlah)) {
            return false;
        }

        DB::transaction(function () use ($tagihan, $pelanggan): void {
            $this->ledger->debit(
                pelanggan: $pelanggan,
                jumlah: $tagihan->jumlah,
                sumber: 'auto_renew',
                referensi: $tagihan->nomor_tagihan,
                keterangan: "Pelunasan otomatis tagihan {$tagihan->nomor_tagihan}.",
            );

            Pembayaran::query()->create([
                'tenant_id' => $pelanggan->tenant_id,
                'tagihan_id' => $tagihan->getKey(),
                'pelanggan_id' => $pelanggan->getKey(),
                'jumlah' => $tagihan->jumlah,
                'tanggal_bayar' => now()->toDateString(),
                'metode_pembayaran' => 'saldo',
                'referensi' => $tagihan->nomor_tagihan,
                'status' => PaymentStatus::Berhasil,
                'keterangan' => 'Dibayar otomatis dari saldo pelanggan.',
            ]);

            $tagihan->forceFill([
                'status' => InvoiceStatus::Lunas,
                'tanggal_bayar' => now()->toDateString(),
            ])->save();
        });

        return true;
    }

    /**
     * Satu putaran auto-renew untuk seluruh tenant aktif.
     *
     * Hanya pelanggan dengan auto_renew = true. Mengembalikan jumlah tagihan
     * yang berhasil dilunasi.
     */
    public function runForAllTenants(): int
    {
        $paid = 0;

        $this->tenants->forEachActiveTenant(function () use (&$paid): void {
            $paid += $this->runForCurrentTenant();
        });

        return $paid;
    }

    /**
     * Melunasi tagihan pelanggan auto-renew di tenant yang sedang aktif.
     */
    public function runForCurrentTenant(): int
    {
        $paid = 0;

        $pelanggans = Pelanggan::query()
            ->where('auto_renew', true)
            ->where('saldo', '>', 0)
            ->get();

        foreach ($pelanggans as $pelanggan) {
            $tagihans = $pelanggan->tagihan()
                ->whereIn('status', [
                    InvoiceStatus::BelumBayar->value,
                    InvoiceStatus::Sebagian->value,
                    InvoiceStatus::Terlambat->value,
                ])
                ->orderBy('jatuh_tempo')
                ->get();

            foreach ($tagihans as $tagihan) {
                if (! $this->pay($tagihan)) {
                    // Saldo tidak cukup untuk tagihan ini; hentikan pelanggan ini
                    // agar tidak melompati tagihan yang lebih lama.
                    continue;
                }

                $paid++;
            }
        }

        return $paid;
    }

    private function pelangganOf(Tagihan $tagihan): ?Pelanggan
    {
        return Pelanggan::query()->find($tagihan->pelanggan_id);
    }

    /**
     * Kolom tagihans.status tidak di-cast ke enum, jadi dibandingkan sebagai
     * string terhadap nilai enum InvoiceStatus.
     */
    private function isOutstanding(Tagihan $tagihan): bool
    {
        return in_array($tagihan->status, [
            InvoiceStatus::BelumBayar->value,
            InvoiceStatus::Sebagian->value,
            InvoiceStatus::Terlambat->value,
        ], true);
    }
}
