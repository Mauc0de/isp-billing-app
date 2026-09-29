<?php

use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/portal', function () {
    return view('portal.index');
})->name('portal');

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);

    // Pakai guard Laravel, bukan session manual, supaya $request->user()
    // bekerja untuk middleware ResolveTenant/EnsureRole/EnsurePermission.
    if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    $request->session()->regenerate();

    return redirect('/dashboard');
});

Route::get('/register', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }

    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    // tenant_id berisi ULID, bukan angka. Pada tahap pilot seluruh pengguna
    // bergabung ke tenant aktif pertama.
    $tenant = Tenant::where('is_active', true)->orderBy('created_at')->first();

    if ($tenant === null) {
        return back()->withErrors(['email' => 'Belum ada tenant aktif. Hubungi administrator.']);
    }

    // Password di-hash oleh cast 'hashed' pada model User.
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password,
        'tenant_id' => $tenant->id,
        'is_active' => true,
    ]);

    // Login lewat guard, bukan hanya session manual, supaya middleware 'auth'
    // dan 'tenant' mengenali user ini.
    Auth::login($user);
    $request->session()->regenerate();

    return redirect('/dashboard');
});

Route::get('/admin/login', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }

    return view('auth.admin_login');
})->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);

    if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    $request->session()->regenerate();

    return redirect('/dashboard');
});

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth', 'tenant'])->group(function () {
    // Isolasi tenant ditangani global scope BelongsToTenant yang diaktifkan
    // oleh middleware 'tenant'. Karena itu query di bawah tidak perlu lagi
    // memfilter tenant_id secara manual.

    Route::get('/dashboard', function () {
        $totalPelanggan = Pelanggan::count();
        $totalPaket = Paket::count();
        $tagihanBelumBayar = Tagihan::where('status', 'belum_bayar')->count();
        $pembayaranBulanIni = Pembayaran::whereMonth('tanggal_bayar', now()->month)->sum('jumlah');

        return view('dashboard.index', compact(
            'totalPelanggan',
            'totalPaket',
            'tagihanBelumBayar',
            'pembayaranBulanIni'
        ));
    })->name('dashboard');

    Route::get('/pelanggan', function () {
        $pelanggan = Pelanggan::with('paket')->get();

        return view('pelanggan.index', compact('pelanggan'));
    })->name('pelanggan.index');

    Route::get('/paket', function () {
        $paket = Paket::get();

        return view('paket.index', compact('paket'));
    })->name('paket.index');

    Route::get('/tagihan', function () {
        $tagihan = Tagihan::with('pelanggan')->get();

        return view('tagihan.index', compact('tagihan'));
    })->name('tagihan.index');

    Route::get('/pembayaran', function () {
        $pembayaran = Pembayaran::with(['pelanggan', 'tagihan'])->get();

        return view('pembayaran.index', compact('pembayaran'));
    })->name('pembayaran.index');

    Route::get('/laporan', function () {
        $totalPendapatan = Pembayaran::where('status', 'berhasil')->sum('jumlah');
        $totalTagihan = Tagihan::sum('jumlah');
        $tagihanLunas = Tagihan::where('status', 'lunas')->count();
        $tagihanBelumBayar = Tagihan::where('status', 'belum_bayar')->count();

        return view('laporan.index', compact(
            'totalPendapatan',
            'totalTagihan',
            'tagihanLunas',
            'tagihanBelumBayar'
        ));
    })->name('laporan.index');

    Route::get('/pengaturan', function () {
        return view('pengaturan.index');
    })->name('pengaturan.index');

});
