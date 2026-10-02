<?php

namespace App\Contracts;

use App\Models\PaymentRequest;
use App\Models\Pelanggan;
use App\Models\Tagihan;

/**
 * Gateway pembayaran.
 *
 * Implementasi boleh berupa transfer manual (unggah bukti) maupun penyedia
 * online (Tripay/Midtrans/Xendit). Kontraknya sengaja tipis: membuat satu
 * permintaan pembayaran dan (opsional) menerima callback dari provider.
 */
interface PaymentGateway
{
    /**
     * Nama teknis gateway, dicocokkan dengan kolom payment_requests.provider.
     */
    public function name(): string;

    /**
     * Membuat permintaan pembayaran. Gateway online boleh mengembalikan
     * checkout_url; transfer manual mengembalikan instruksi rekening.
     */
    public function createRequest(
        Pelanggan $pelanggan,
        int $jumlah,
        string $tujuan,
        ?Tagihan $tagihan = null,
    ): PaymentRequest;
}
