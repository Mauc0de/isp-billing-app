@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto mt-16 p-8 bg-white rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Masuk</h2>
    <form method="POST" action="/login">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="email">Email</label>
            <input class="w-full border rounded px-3 py-2" type="email" name="email" id="email" required>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1" for="password">Kata Sandi</label>
            <input class="w-full border rounded px-3 py-2" type="password" name="password" id="password" required>
        </div>
        <button class="w-full bg-blue-900 text-white py-2 rounded hover:bg-blue-800 transition">Masuk</button>
    </form>
    <p class="mt-4 text-center text-sm">
        Belum punya akun? <a href="/register" class="text-blue-600 hover:underline">Daftar</a>
    </p>
</div>
@endsection