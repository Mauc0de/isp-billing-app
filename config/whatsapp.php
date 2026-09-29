<?php

use App\Enums\WhatsappProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Provider WhatsApp
    |--------------------------------------------------------------------------
    |
    | Default provider saat tenant belum memilih sendiri. "disabled" berarti
    | pesan tetap dicatat di wa_notifications tapi tidak benar-benar dikirim,
    | sehingga alur otomatis tetap bisa diuji sebelum token dari kantor masuk.
    |
    */

    'default_provider' => env('WHATSAPP_DEFAULT_PROVIDER', WhatsappProvider::Disabled->value),

    'timeout' => (int) env('WHATSAPP_TIMEOUT', 20),

    /*
    |--------------------------------------------------------------------------
    | Fonnte
    |--------------------------------------------------------------------------
    |
    | POST {base_url}/send
    | Header : Authorization: {token}   (tanpa prefix "Bearer")
    | Body   : target, message, countryCode  (form-encoded)
    | Sukses : {"status":true,"id":["80367170"],"detail":"success! message in queue"}
    | Gagal  : {"status":false,"reason":"token invalid"}
    |
    */

    'fonnte' => [
        'base_url' => env('FONNTE_BASE_URL', 'https://api.fonnte.com'),
        'token' => env('FONNTE_TOKEN'),
        // "0" mematikan penggantian awalan 0 menjadi kode negara, karena
        // nomor tujuan sudah dinormalisasi ke format 62xxx oleh sistem.
        'country_code' => env('FONNTE_COUNTRY_CODE', '0'),
        'connect_only' => (bool) env('FONNTE_CONNECT_ONLY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Wablas
    |--------------------------------------------------------------------------
    |
    | POST {base_url}/send-message
    | Header : Authorization: {token}.{secret_key}
    | Body   : phone, message, ref_id  (form-encoded)
    | Sukses : {"status":true,"data":{"messages":[{"id":"5be4...","status":"pending"}]}}
    |
    | Base URL resmi saat ini memakai console.wablas.com; domain lama
    | wablas.com/api masih menerima endpoint yang sama.
    |
    */

    'wablas' => [
        'base_url' => env('WABLAS_BASE_URL', 'https://console.wablas.com/api'),
        'token' => env('WABLAS_TOKEN'),
        'secret_key' => env('WABLAS_SECRET_KEY'),
        // Disimpan sebagai ref_id supaya pesan bisa dikorelasikan dari sisi Wablas.
        'send_ref_id' => (bool) env('WABLAS_SEND_REF_ID', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Pesan
    |--------------------------------------------------------------------------
    |
    | Placeholder yang didukung:
    |   {customer} {customer_number} {tenant} {package} {router}
    |   {invoice_number} {amount} {due_date} {overdue_days} {reason}
    |
    | Ini hanya default. Tenant bisa menimpanya lewat tabel settings, dan
    | itu yang dibaca oleh App\Settings\TenantSettings.
    |
    */

    'templates' => [
        'suspend' => 'Halo {customer}, layanan internet Anda di {tenant} dinonaktifkan sementara karena tagihan belum lunas. Total tagihan {amount} jatuh tempo {due_date}. Silakan lakukan pembayaran untuk segera mengaktifkan kembali. Terima kasih.',

        'reactivate' => 'Halo {customer}, pembayaran Anda sudah kami terima. Layanan internet Anda di {tenant} sudah aktif kembali. Terima kasih.',

        'due_reminder' => 'Halo {customer}, tagihan {invoice_number} sebesar {amount} akan jatuh tempo pada {due_date}. Silakan lakukan pembayaran sebelum tanggal tersebut agar layanan tidak terputus. Terima kasih.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengiriman
    |--------------------------------------------------------------------------
    */

    'max_attempts' => (int) env('WHATSAPP_MAX_ATTEMPTS', 3),

    'backoff_seconds' => (int) env('WHATSAPP_BACKOFF_SECONDS', 120),

];
