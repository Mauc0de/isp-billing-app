<?php

use App\Livewire\Customers\Index;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/customers', Index::class)
    ->name('customers.index');

Route::get('/pelanggan', function () {
    return view('pelanggan.index');
});

Route::get('/pembayaran', function () {
    return view('pembayaran.index');
});

Route::get('/tagihan', function () {
    return view('tagihan.index');
});

Route::get('/paket', function () {
    return view('paket.index');
});

Route::get('/laporan', function () {
    return view('laporan.index');
});

Route::get('/pengaturan', function () {
    return view('pengaturan.index');
});

Route::get('/logout', function () {
    return redirect('/');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::post('/login', function () {
    return redirect('/dashboard');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::post('/register', function () {
    return redirect('/dashboard');
});