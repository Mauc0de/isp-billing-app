<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('welcome');

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', fn () => view('auth.register'))->name('register');
    Route::post('/register', [AuthenticatedSessionController::class, 'register'])->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
|
| Middlewaver `tenant` menurunkan TenantContext dari user yang sedang login
| dan menolak user atau tenant nonaktif. Route bertanda `permission`
| memakai Gate yang dievaluasi di dalam konteks tenant tersebut.
|
*/

Route::middleware(['auth', 'tenant'])->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard.index'))
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/pelanggan', fn () => view('pelanggan.index'))
        ->middleware('permission:customers.view')
        ->name('pelanggan');

    Route::get('/paket', fn () => view('paket.index'))
        ->middleware('permission:packages.view')
        ->name('paket');

    Route::get('/tagihan', fn () => view('tagihan.index'))
        ->middleware('permission:invoices.view')
        ->name('tagihan');

    Route::get('/pembayaran', fn () => view('pembayaran.index'))
        ->middleware('permission:payments.view')
        ->name('pembayaran');

    Route::get('/laporan', fn () => view('laporan.index'))
        ->middleware('permission:reports.view')
        ->name('laporan');

    Route::get('/pengaturan', fn () => view('pengaturan.index'))
        ->middleware('permission:settings.view')
        ->name('pengaturan');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
