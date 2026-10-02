<?php

namespace App\Payments;

use App\Billing\SaldoLedger;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentRequestPurpose;
use App\Enums\PaymentRequestStatus;
use App\Enums\PaymentStatus;
use App\Models\PaymentRequest;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Memverifikasi (atau menolak) permintaan pembayaran.
 *
 * Verifikasi bersifat idempoten berdasarkan status: permintaan yang sudah
 * terverifikasi tidak akan diproses dua kali, sehingga admin yang menekan
 * tombol dua kali tidak menggandakan saldo.
 */
class PaymentRequestVerifier
{
    public function __construct(private readonly SaldoLedger $ledger) {}

    /**
     * Tandai terverifikasi lalu terapkan dampaknya ke saldo atau tagihan.
     */
    public function approve(PaymentRequest $request, ?User $actor = null, ?string $note = null): PaymentRequest
    {
        if (! $request->status->isPending()) {
            throw new LogicException('Permintaan ini sudah diproses sebelumnya.');
        }

        return DB::transaction(function () use ($request, $actor, $note): PaymentRequest {
            $pelanggan = $request->pelanggan;

            if ($pelanggan === null) {
                throw new LogicException('Pelanggan untuk permintaan ini tidak ditemukan.');
            }

            match ($request->tujuan) {
                PaymentRequestPurpose::TopUpSaldo => $this->ledger->credit(
                    pelanggan: $pelanggan,
                    jumlah: $request->jumlah,
                    sumber: 'payment_request',
                    referensi: $request->getKey(),
                    keterangan: 'Top-up saldo terverifikasi.',
                ),
                PaymentRequestPurpose::BayarTagihan => $this->settleInvoice($request, $pelanggan),
            };

            $request->forceFill([
                'status' => PaymentRequestStatus::Terverifikasi,
                'verified_by_id' => $actor?->getKey(),
                'verified_at' => now(),
                'catatan_admin' => $note,
            ])->save();

            return $request;
        });
    }

    public function reject(PaymentRequest $request, ?User $actor = null, ?string $note = null): PaymentRequest
    {
        if (! $request->status->isPending()) {
            throw new LogicException('Permintaan ini sudah diproses sebelumnya.');
        }

        $request->forceFill([
            'status' => PaymentRequestStatus::Ditolak,
            'verified_by_id' => $actor?->getKey(),
            'verified_at' => now(),
            'catatan_admin' => $note,
        ])->save();

        return $request;
    }

    /**
     * Pelunasan tagihan langsung dari pembayaran pelanggan (bukan saldo).
     */
    private function settleInvoice(PaymentRequest $request, $pelanggan): void
    {
        $tagihan = $request->tagihan;

        if ($tagihan === null) {
            throw new LogicException('Tagihan untuk permintaan ini tidak ditemukan.');
        }

        $tagihan->forceFill([
            'status' => InvoiceStatus::Lunas,
            'tanggal_bayar' => now()->toDateString(),
        ])->save();

        Pembayaran::query()->create([
            'tenant_id' => $pelanggan->tenant_id,
            'tagihan_id' => $tagihan->getKey(),
            'pelanggan_id' => $pelanggan->getKey(),
            'jumlah' => $request->jumlah,
            'tanggal_bayar' => now()->toDateString(),
            'metode_pembayaran' => 'transfer_manual',
            'referensi' => $request->getKey(),
            'status' => PaymentStatus::Berhasil,
            'keterangan' => 'Pembayaran transfer manual terverifikasi.',
        ]);
    }
}
