<?php

namespace App\Payments;

use App\Contracts\PaymentGateway;
use App\Enums\PaymentRequestPurpose;
use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\Pelanggan;
use App\Models\Tagihan;

/**
 * Gateway transfer manual (gratis, tanpa penyedia pihak ketiga).
 *
 * Pelanggan mentransfer ke rekening ISP lalu mengunggah bukti. Permintaan
 * disimpan berstatus "menunggu" sampai admin memverifikasi. Ini alur default
 * sehingga aplikasi bisa dipakai tanpa biaya per transaksi.
 */
class ManualTransferGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'transfer_manual';
    }

    public function createRequest(
        Pelanggan $pelanggan,
        int $jumlah,
        string $tujuan,
        ?Tagihan $tagihan = null,
    ): PaymentRequest {
        return PaymentRequest::query()->create([
            'tenant_id' => $pelanggan->tenant_id,
            'pelanggan_id' => $pelanggan->getKey(),
            'tagihan_id' => $tagihan?->getKey(),
            'tujuan' => PaymentRequestPurpose::from($tujuan),
            'jumlah' => $jumlah,
            'metode' => $this->name(),
            'provider' => $this->name(),
            'status' => PaymentRequestStatus::Menunggu,
        ]);
    }
}
