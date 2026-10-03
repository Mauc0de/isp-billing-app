<?php

namespace App\Billing;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Tenancy\TenantRunner;
use Illuminate\Support\Carbon;

/**
 * Generator tagihan bulanan untuk semua pelanggan aktif.
 *
 * Dijalankan tiap bulan oleh scheduler (dan bisa manual lewat command
 * isp:generate-invoices). Pelanggan cukup dibuat sekali; tagihannya yang
 * dibuatkan otomatis setiap bulan sesuai harga paketnya.
 *
 * Idempoten: pelanggan yang sudah punya tagihan di bulan acuan dilewati,
 * jadi aman dijalankan ulang.
 */
class InvoiceGenerator
{
    public function __construct(private readonly TenantRunner $tenants) {}

    /**
     * @return int jumlah tagihan yang dibuat
     */
    public function run(?Carbon $bulan = null): int
    {
        $bulan ??= now();
        $created = 0;

        $this->tenants->forEachActiveTenant(function () use ($bulan, &$created): void {
            $created += $this->generateForCurrentTenant($bulan);
        });

        return $created;
    }

    public function generateForCurrentTenant(Carbon $bulan): int
    {
        $created = 0;

        Pelanggan::query()
            ->where('status', CustomerStatus::Aktif->value)
            ->with('paket')
            ->each(function (Pelanggan $pelanggan) use ($bulan, &$created): void {
                if ($this->alreadyHasInvoice($pelanggan, $bulan)) {
                    return;
                }

                $paket = $pelanggan->paket;

                if ($paket === null || (int) $paket->harga <= 0) {
                    return;
                }

                if (! $this->isBillingMonth($pelanggan, $paket->billing_cycle, $bulan)) {
                    return;
                }

                Tagihan::query()->create([
                    'tenant_id' => $pelanggan->tenant_id,
                    'pelanggan_id' => $pelanggan->getKey(),
                    'nomor_tagihan' => $this->invoiceNumber($pelanggan, $bulan),
                    'jumlah' => (int) $paket->harga,
                    'tanggal_terbit' => $bulan->copy()->startOfMonth()->toDateString(),
                    'jatuh_tempo' => $bulan->copy()->day(10)->toDateString(),
                    'status' => InvoiceStatus::BelumBayar->value,
                    'keterangan' => sprintf('Tagihan %s %s (%s).', $paket->nama_paket, $bulan->translatedFormat('F Y'), $pelanggan->nama),
                ]);

                $created++;
            });

        return $created;
    }

    /**
     * Apakah bulan ini periode tagihan untuk pelanggan dengan siklus tertentu.
     *
     * Bulanan: selalu. Kuartalan: tiap 3 bulan dari bulan tanggal_aktif.
     * Tahunan: bulan yang sama dengan tanggal_aktif. Custom diperlakukan
     * sebagai bulanan.
     */
    private function isBillingMonth(Pelanggan $pelanggan, ?\App\Enums\BillingCycle $cycle, Carbon $bulan): bool
    {
        $anchor = $pelanggan->tanggal_aktif ?? Carbon::create(2026, 1, 1);

        return match ($cycle) {
            \App\Enums\BillingCycle::Monthly, \App\Enums\BillingCycle::Custom, null => true,
            \App\Enums\BillingCycle::Quarterly => (($bulan->year * 12 + $bulan->month) - ($anchor->year * 12 + $anchor->month)) % 3 === 0,
            \App\Enums\BillingCycle::Yearly => $bulan->month === $anchor->month,
        };
    }

    private function alreadyHasInvoice(Pelanggan $pelanggan, Carbon $bulan): bool
    {
        return $pelanggan->tagihan()
            ->whereYear('tanggal_terbit', $bulan->year)
            ->whereMonth('tanggal_terbit', $bulan->month)
            ->exists();
    }

    private function invoiceNumber(Pelanggan $pelanggan, Carbon $bulan): string
    {
        return sprintf(
            'INV-%s-%s-%s',
            $bulan->format('Ym'),
            strtoupper(substr((string) $pelanggan->tenant_id, -4)),
            strtoupper(substr((string) $pelanggan->getKey(), -6)),
        );
    }
}
