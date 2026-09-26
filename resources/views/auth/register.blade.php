@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto mt-16 p-8 bg-white rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Daftar</h2>
    <form method="POST" action="/register">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="name">Nama</label>
            <input class="w-full border rounded px-3 py-2" type="text" name="name" id="name" required>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1" for="email">Email</label>
            <input class="w-full border rounded px-3 py-2" type="email" name="email" id="email" required>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1" for="password">Kata Sandi</label>
            <input class="w-full border rounded px-3 py-2" type="password" name="password" id="password" required>
        </div>
        <button class="w-full bg-blue-900 text-white py-2 rounded hover:bg-blue-800 transition">Daftar</button>
    </form>
    <p class="mt-4 text-center text-sm">
        Sudah punya akun? <a href="/login" class="text-blue-600 hover:underline">Masuk</a>
    </p>
</div>
@endsection