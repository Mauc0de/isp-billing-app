<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\Paket;
use App\Livewire\Portal\Pembayaran;
use App\Livewire\Portal\Tagihan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
|
| Halaman yang boleh diakses tanpa login.
|
*/

Route::get('/', fn () => view('welcome'))->name('welcome');

/*
|--------------------------------------------------------------------------
| Guest (belum login)
|--------------------------------------------------------------------------
|
| Satu pintu masuk untuk admin maupun pelanggan. Setelah login, pengguna
| diarahkan otomatis: pelanggan ke portal, staf ke dashboard.
|
*/

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', fn () => view('auth.register'))->name('register');
    Route::post('/register', [AuthenticatedSessionController::class, 'register'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Portal Pelanggan
|--------------------------------------------------------------------------
|
| Khusus pengguna yang tertaut ke data pelanggan. Isi portal mengikuti
| pelanggan milik user yang sedang login, bukan data milik tenant secara
| keseluruhan.
|
*/

Route::middleware(['auth', 'tenant'])->prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', Dashboard::class)->name('index');
    Route::get('/tagihan', Tagihan::class)->name('tagihan');
    Route::get('/pembayaran', Pembayaran::class)->name('pembayaran');
    Route::get('/paket', Paket::class)->name('paket');
});

/*
|--------------------------------------------------------------------------
| Area Staf (dashboard)
|--------------------------------------------------------------------------
|
| Middleware `tenant` menurunkan TenantContext dari user yang login dan
| menolak user/tenant nonaktif. Akses dibatasi lewat permission: akun
| pelanggan tanpa role tidak punya izin, sehingga otomatis ditolak 403.
|
*/

Route::middleware(['auth', 'tenant'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/pelanggan', [BillingController::class, 'pelanggan'])
        ->middleware('permission:customers.view')
        ->name('pelanggan.index');

    Route::get('/paket', [BillingController::class, 'paket'])
        ->middleware('permission:packages.view')
        ->name('paket.index');

    Route::get('/tagihan', [BillingController::class, 'tagihan'])
        ->middleware('permission:invoices.view')
        ->name('tagihan.index');

    Route::get('/pembayaran', [BillingController::class, 'pembayaran'])
        ->middleware('permission:payments.view')
        ->name('pembayaran.index');

    Route::get('/laporan', [BillingController::class, 'laporan'])
        ->middleware('permission:reports.view')
        ->name('laporan.index');

    Route::get('/pengaturan', [BillingController::class, 'pengaturan'])
        ->middleware('permission:settings.view')
        ->name('pengaturan.index');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
