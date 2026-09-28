<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    if (session('user_id')) return redirect('/dashboard');
    return view('auth.login');
});

Route::post('/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    $request->session()->put('user_id', 1);
    $request->session()->put('user_email', $request->email);
    $request->session()->put('user_role', $request->input('role', 'admin'));
    return redirect('/dashboard');
});

Route::get('/register', function () {
    if (session('user_id')) return redirect('/dashboard');
    return view('auth.register');
});

Route::post('/register', function (Request $request) {
    $request->validate(['name' => 'required', 'email' => 'required|email', 'password' => 'required|min:6']);
    $request->session()->put('user_id', 1);
    $request->session()->put('user_email', $request->email);
    $request->session()->put('user_name', $request->name);
    return redirect('/dashboard');
});

Route::get('/logout', function (Request $request) {
    $request->session()->forget(['user_id','user_email','user_name','user_role']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
});

Route::get('/dashboard', function () {
    if (!session('user_id')) return redirect('/login');
    return view('dashboard.index');
});

Route::get('/pelanggan', function () {
    if (!session('user_id')) return redirect('/login');
    return view('pelanggan.index');
});

Route::get('/pembayaran', function () {
    if (!session('user_id')) return redirect('/login');
    return view('pembayaran.index');
});

Route::get('/tagihan', function () {
    if (!session('user_id')) return redirect('/login');
    return view('tagihan.index');
});

Route::get('/paket', function () {
    if (!session('user_id')) return redirect('/login');
    return view('paket.index');
});

Route::get('/laporan', function () {
    if (!session('user_id')) return redirect('/login');
    return view('laporan.index');
});

Route::get('/pengaturan', function () {
    if (!session('user_id')) return redirect('/login');
    return view('pengaturan.index');
});
