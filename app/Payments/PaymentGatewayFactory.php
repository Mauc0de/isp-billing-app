<?php

namespace App\Payments;

use App\Contracts\PaymentGateway;

/**
 * Memilih gateway pembayaran.
 *
 * Untuk saat ini hanya tersedia transfer manual (gratis). Adapter online
 * (Tripay/Midtrans/Xendit) tinggal ditambahkan di match() ini.
 */
class PaymentGatewayFactory
{
    public function make(?string $name = null): PaymentGateway
    {
        return match ($name ?? 'transfer_manual') {
            default => new ManualTransferGateway,
        };
    }
}
