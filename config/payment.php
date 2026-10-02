<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gateway Pembayaran
    |--------------------------------------------------------------------------
    |
    | "transfer_manual" adalah default dan sepenuhnya gratis: pelanggan
    | mentransfer ke rekening ISP lalu mengunggah bukti, admin memverifikasi.
    | Adapter online (Tripay/Midtrans/Xendit) dapat ditambahkan kemudian tanpa
    | mengubah skema database.
    |
    */

    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'transfer_manual'),

    /*
    |--------------------------------------------------------------------------
    | Rekening Tujuan Transfer Manual
    |--------------------------------------------------------------------------
    |
    | Ditampilkan di portal pelanggan saat mengajukan pembayaran. Bisa ditimpa
    | per tenant lewat tabel settings (key: payment.bank_*).
    |
    */

    'bank' => [
        'nama' => env('PAYMENT_BANK_NAME', 'BCA'),
        'nomor' => env('PAYMENT_BANK_NUMBER', '1234567890'),
        'atas_nama' => env('PAYMENT_BANK_HOLDER', 'PT ISP Indonesia'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Unggahan Bukti
    |--------------------------------------------------------------------------
    */

    'bukti' => [
        'disk' => env('PAYMENT_PROOF_DISK', 'local'),
        'max_kb' => (int) env('PAYMENT_PROOF_MAX_KB', 4096),
    ],

];
