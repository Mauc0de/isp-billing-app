<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentRequestController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\SaldoController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\Paket;
use App\Livewire\Portal\Pembayaran;
use App\Livewire\Portal\PembayaranSaya;
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
    Route::get('/pembayaran-saya', PembayaranSaya::class)->name('pembayaran-saya');
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

    Route::get('/tagihan/{tagihan}/pdf', [BillingController::class, 'tagihanPdf'])
        ->middleware('permission:invoices.view')
        ->name('tagihan.pdf');

    Route::get('/pembayaran', [BillingController::class, 'pembayaran'])
        ->middleware('permission:payments.view')
        ->name('pembayaran.index');

    Route::get('/router', [RouterController::class, 'index'])
        ->middleware('permission:routers.view')
        ->name('router.index');

    Route::post('/router', [RouterController::class, 'store'])
        ->middleware('permission:routers.manage')
        ->name('router.store');

    Route::patch('/router/{router}', [RouterController::class, 'update'])
        ->middleware('permission:routers.manage')
        ->name('router.update');

    Route::post('/router/{router}/test', [RouterController::class, 'test'])
        ->middleware('permission:routers.manage')
        ->name('router.test');

    Route::post('/router/{router}/toggle', [RouterController::class, 'toggle'])
        ->middleware('permission:routers.manage')
        ->name('router.toggle');

    Route::delete('/router/{router}', [RouterController::class, 'destroy'])
        ->middleware('permission:routers.manage')
        ->name('router.destroy');

    Route::get('/laporan', [BillingController::class, 'laporan'])
        ->middleware('permission:reports.view')
        ->name('laporan.index');

    Route::get('/laporan/export', [BillingController::class, 'export'])
        ->middleware('permission:reports.export')
        ->name('laporan.export');

    Route::get('/pengeluaran', [BillingController::class, 'pengeluaranIndex'])
        ->middleware('permission:expenses.view')
        ->name('pengeluaran.index');

    Route::post('/pengeluaran', [BillingController::class, 'pengeluaranStore'])
        ->middleware('permission:expenses.manage')
        ->name('pengeluaran.store');

    Route::delete('/pengeluaran/{pengeluaran}', [BillingController::class, 'pengeluaranDestroy'])
        ->middleware('permission:expenses.manage')
        ->name('pengeluaran.destroy');

    Route::get('/pengaturan', [SettingController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('pengaturan.index');

    Route::post('/pengaturan', [SettingController::class, 'update'])
        ->middleware('permission:settings.update')
        ->name('pengaturan.update');

    /*
    | Manajemen Pengguna
    |------------------
    | Halaman ini dipakai untuk menambah staf, mengubah role, mengaktifkan /
    | menonaktifkan akun (termasuk menyetujui pendaftaran pelanggan), dan
    | mereset password. Dijaga oleh permission users.*.
    */
    Route::get('/pengguna', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::post('/pengguna', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::patch('/pengguna/{user}/role', [UserController::class, 'updateRole'])
        ->middleware('permission:users.update')
        ->name('users.role');

    Route::patch('/pengguna/{user}/status', [UserController::class, 'toggleActive'])
        ->middleware('permission:users.update')
        ->name('users.status');

    Route::patch('/pengguna/{user}/password', [UserController::class, 'resetPassword'])
        ->middleware('permission:users.update')
        ->name('users.password');

    /*
    | Voucher Hotspot
    |---------------
    | Generator kupon prepaid ala PHPNuxBill: buat batch, cetak, dan hapus
    | voucher yang belum terpakai.
    */
    Route::get('/voucher', [VoucherController::class, 'index'])
        ->middleware('permission:vouchers.view')
        ->name('vouchers.index');

    Route::post('/voucher', [VoucherController::class, 'store'])
        ->middleware('permission:vouchers.create')
        ->name('vouchers.store');

    Route::get('/voucher/batch/{batch}', [VoucherController::class, 'batch'])
        ->middleware('permission:vouchers.view')
        ->name('vouchers.batch');

    Route::delete('/voucher/{voucher}', [VoucherController::class, 'destroy'])
        ->middleware('permission:vouchers.delete')
        ->name('vouchers.destroy');

    /*
    | Saldo & Auto-renew
    |-------------------
    | Saldo pelanggan, riwayat mutasi, top-up manual, dan pelunasan tagihan
    | dari saldo. Auto-renew otomatis ikut berjalan di pemindaian harian.
    */
    Route::get('/saldo', [SaldoController::class, 'index'])
        ->middleware('permission:saldo.view')
        ->name('saldo.index');

    Route::get('/saldo/{pelanggan}', [SaldoController::class, 'show'])
        ->middleware('permission:saldo.view')
        ->name('saldo.show');

    Route::post('/saldo/{pelanggan}/topup', [SaldoController::class, 'topUp'])
        ->middleware('permission:saldo.manage')
        ->name('saldo.topup');

    Route::patch('/saldo/{pelanggan}/auto-renew', [SaldoController::class, 'toggleAutoRenew'])
        ->middleware('permission:saldo.manage')
        ->name('saldo.auto-renew');

    Route::post('/saldo/tagihan/{tagihan}/bayar', [SaldoController::class, 'payInvoice'])
        ->middleware('permission:saldo.manage')
        ->name('saldo.bayar');

    /*
    | Verifikasi Pembayaran
    |---------------------
    | Permintaan pembayaran dari pelanggan (transfer manual + bukti). Admin
    | menyetujui untuk menerapkan ke saldo/tagihan, atau menolaknya.
    */
    Route::get('/pembayaran-masuk', [PaymentRequestController::class, 'index'])
        ->middleware('permission:payment_requests.view')
        ->name('payment-requests.index');

    Route::get('/pembayaran-masuk/{paymentRequest}/bukti', [PaymentRequestController::class, 'proof'])
        ->middleware('permission:payment_requests.view')
        ->name('payment-requests.proof');

    Route::post('/pembayaran-masuk/{paymentRequest}/setujui', [PaymentRequestController::class, 'approve'])
        ->middleware('permission:payment_requests.verify')
        ->name('payment-requests.approve');

    Route::post('/pembayaran-masuk/{paymentRequest}/tolak', [PaymentRequestController::class, 'reject'])
        ->middleware('permission:payment_requests.verify')
        ->name('payment-requests.reject');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
