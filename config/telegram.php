<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot
    |--------------------------------------------------------------------------
    |
    | Notifikasi ke admin ISP. Token bot dibuat lewat @BotFather, chat ID
    | diperoleh dari @userinfobot (atau grup admin). Nilai ini disimpan di .env
    | dan TIDAK masuk database karena kredensial.
    |
    | Bila token kosong, gateway otomatis memakai NullTelegramGateway sehingga
    | notifikasi hanya dicatat tanpa dikirim (aman untuk pengembangan).
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    'chat_id' => env('TELEGRAM_CHAT_ID'),

    'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org'),

    'timeout' => (int) env('TELEGRAM_TIMEOUT', 15),

    /* 'HTML' atau 'Markdown'. HTML lebih aman karena tidak banyak karakter khusus. */
    'parse_mode' => env('TELEGRAM_PARSE_MODE', 'HTML'),

    /*
    |--------------------------------------------------------------------------
    | Tema Emoji
    |--------------------------------------------------------------------------
    |
    | Dipakai di awal pesan supaya admin langsung tahu jenis kejadiannya.
    |
    */

    'emoji' => [
        'customer_created' => '🆕',
        'suspended' => '⛔',
        'reactivated' => '✅',
        'payment_incoming' => '💰',
        'router_offline' => '📡',
        'overdue_scan' => '📊',
    ],

];
