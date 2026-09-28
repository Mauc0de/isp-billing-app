<?php

use App\Enums\RouterSuspendMethod;

return [

    /*
    |--------------------------------------------------------------------------
    | RouterOS API
    |--------------------------------------------------------------------------
    |
    | Setiap router diakses lewat IP privat di jaringan WireGuard, bukan lewat
    | port API yang diekspos ke internet. Nilai di sini jadi default; kolom
    | pada tabel routers menimpa per router.
    |
    */

    'default_port' => (int) env('MIKROTIK_DEFAULT_PORT', 8728),

    'default_suspend_method' => env(
        'MIKROTIK_DEFAULT_SUSPEND_METHOD',
        RouterSuspendMethod::PppSecret->value,
    ),

    'default_address_list' => env('MIKROTIK_DEFAULT_ADDRESS_LIST', 'satak-blocklist'),

    /*
    |--------------------------------------------------------------------------
    | Timeout & Retry
    |--------------------------------------------------------------------------
    |
    | Nilai sengaja kecil: pemindaian dijalankan untuk banyak router sekaligus
    | lewat queue, jadi satu router yang lambat tidak boleh menahan yang lain.
    |
    */

    'connect_timeout' => (int) env('MIKROTIK_CONNECT_TIMEOUT', 5),

    'read_timeout' => (int) env('MIKROTIK_READ_TIMEOUT', 10),

    'attempts' => (int) env('MIKROTIK_ATTEMPTS', 2),

    'retry_delay' => (int) env('MIKROTIK_RETRY_DELAY', 1),

    'legacy_login' => (bool) env('MIKROTIK_LEGACY_LOGIN', false),

    /*
    |--------------------------------------------------------------------------
    | Endpoint RouterOS
    |--------------------------------------------------------------------------
    */

    'endpoints' => [
        'identity' => '/system/identity/print',
        'resource' => '/system/resource/print',
        'ppp_secret' => '/ppp/secret',
        'active' => '/ppp/active/print',
        'address_list' => '/ip/firewall/address-list',
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout Pemindaian
    |--------------------------------------------------------------------------
    |
    | Setelah router tidak bisa dihubungi selama beberapa hari, tandai sebagai
    | tidak aktif supaya tidak terus dicoba setiap hari.
    |
    */

    'offline_after_days' => (int) env('MIKROTIK_OFFLINE_AFTER_DAYS', 7),

];
