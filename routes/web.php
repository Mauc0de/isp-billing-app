<?php

use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/portal', function () {
    return view('portal.index');
})->name('portal');

Route::get('/login', function () {
    if (session('user_id')) return redirect('/dashboard');
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    $request->session()->put('user_id', $user->id);
    $request->session()->put('user_email', $user->email);
    $request->session()->put('user_name', $user->name);
    $request->session()->put('tenant_id', $user->tenant_id);

    return redirect('/dashboard');
});

Route::get('/register', function () {
    if (session('user_id')) return redirect('/dashboard');
    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password,
        'tenant_id' => 1,
    ]);

    $request->session()->put('user_id', $user->id);
    $request->session()->put('user_email', $user->email);
    $request->session()->put('user_name', $user->name);
    $request->session()->put('tenant_id', $user->tenant_id);

    return redirect('/dashboard');
});

Route::get('/admin/login', function () {
    if (session('user_id')) return redirect('/dashboard');
    return view('auth.admin_login');
})->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    $request->session()->put('user_id', $user->id);
    $request->session()->put('user_email', $user->email);
    $request->session()->put('user_name', $user->name);
    $request->session()->put('tenant_id', $user->tenant_id);

    return redirect('/dashboard');
});

Route::get('/logout', function (Request $request) {
    $request->session()->forget(['user_id', 'user_email', 'user_name', 'tenant_id']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $totalPelanggan = Pelanggan::where('tenant_id', session('tenant_id'))->count();
        $totalPaket = Paket::where('tenant_id', session('tenant_id'))->count();
        $tagihanBelumBayar = Tagihan::where('tenant_id', session('tenant_id'))
            ->where('status', 'belum_bayar')->count();
        $pembayaranBulanIni = Pembayaran::where('tenant_id', session('tenant_id'))
            ->whereMonth('tanggal_bayar', now()->month)
            ->sum('jumlah');

        return view('dashboard.index', compact(
            'totalPelanggan',
            'totalPaket',
            'tagihanBelumBayar',
            'pembayaranBulanIni'
        ));
    })->name('dashboard');

    Route::get('/pelanggan', function () {
        $pelanggan = Pelanggan::where('tenant_id', session('tenant_id'))
            ->with('paket')
            ->get();
        return view('pelanggan.index', compact('pelanggan'));
    })->name('pelanggan.index');

    Route::get('/paket', function () {
        $paket = Paket::where('tenant_id', session('tenant_id'))->get();
        return view('paket.index', compact('paket'));
    })->name('paket.index');

    Route::get('/tagihan', function () {
        $tagihan = Tagihan::where('tenant_id', session('tenant_id'))
            ->with('pelanggan')
            ->get();
        return view('tagihan.index', compact('tagihan'));
    })->name('tagihan.index');

    Route::get('/pembayaran', function () {
        $pembayaran = Pembayaran::where('tenant_id', session('tenant_id'))
            ->with(['pelanggan', 'tagihan'])
            ->get();
        return view('pembayaran.index', compact('pembayaran'));
    })->name('pembayaran.index');

    Route::get('/laporan', function () {
        $totalPendapatan = Pembayaran::where('tenant_id', session('tenant_id'))
            ->where('status', 'lunas')->sum('jumlah');
        $totalTagihan = Tagihan::where('tenant_id', session('tenant_id'))
            ->sum('jumlah');
        $tagihanLunas = Tagihan::where('tenant_id', session('tenant_id'))
            ->where('status', 'lunas')->count();
        $tagihanBelumBayar = Tagihan::where('tenant_id', session('tenant_id'))
            ->where('status', 'belum_bayar')->count();

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
