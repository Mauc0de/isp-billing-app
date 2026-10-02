@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
<div class="relative min-h-screen">
    <div class="absolute inset-0 bg-cover bg-center z-0" style="background-image: url('{{ asset('latar.jpeg') }}');">
        <div class="absolute inset-0 bg-slate-900/60"></div>
    </div>
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 sm:p-8 backdrop-blur-sm bg-white/90">
                <div class="flex flex-col items-center mb-6 sm:mb-8">
                    <img src="{{ asset('satak.jpeg') }}" alt="Logo SATAK" class="w-14 h-14 rounded-full object-cover mb-4 shadow-sm border border-slate-200">
                    <h1 class="text-2xl font-bold text-slate-900">SATAK ISP</h1>
                    <p class="text-sm text-slate-500 mt-1">Sistem Keuangan ISP - Admin</p>
                </div>
                <div class="bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] p-1 rounded-xl mb-6">
                    <div class="bg-white rounded-xl p-4 sm:p-5">
                        <h2 class="text-base sm:text-lg font-semibold text-slate-900">Masuk sebagai Admin</h2>
                        <p class="text-sm text-slate-600 mt-1">Masukkan kredensial admin</p>
                    </div>
                </div>
                @if($errors->any())
                    <div class="mt-4 bg-red-50 text-red-600 text-xs px-3 py-2.5 rounded-lg border border-red-200">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="/admin/login" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">EMAIL</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6"/></svg>
                            </span>
                            <input type="email" name="email" placeholder="admin@satak.net" required class="w-full pl-10 pr-4 py-3 text-sm border border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 placeholder-slate-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-2">PASSWORD</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="10" rx="2"/><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="login_password" type="password" name="password" placeholder="••••••••" required class="w-full pl-10 pr-11 py-3 text-sm border border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 placeholder-slate-400">
                            <button type="button" onclick="document.getElementById('login_password').type = document.getElementById('login_password').type === 'password' ? 'text' : 'password'" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-end mt-2">
                        <a href="#" class="text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline">Lupa Password?</a>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] hover:from-[#00B8A8] hover:via-[#0059C7] hover:to-[#0046B4] text-white text-sm font-semibold py-3.5 rounded-lg shadow-md transition-all duration-200">Masuk</button>
                </form>
                <div class="mt-6 sm:mt-8 pt-6 border-t border-slate-100">
                    <p class="text-xs text-center text-slate-500">Belum punya akun? <a href="/register" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline">Daftar</a></p>
                    <p class="text-xs text-center text-slate-400 mt-2">&copy; 2026 SATAK. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection