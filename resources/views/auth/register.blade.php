<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SATAK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen relative">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ asset('latar.jpeg') }}')"></div>
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]"></div>
    <div class="relative z-10 min-h-screen flex items-center justify-center py-6 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl border border-white/20 p-6 sm:p-8">
                <div class="flex flex-col items-center mb-6">
                    <img src="{{ asset('satak.jpeg') }}" alt="Logo SATAK" class="w-14 h-14 rounded-2xl object-contain shadow-sm border border-slate-100">
                    <h1 class="mt-3 text-xl font-black tracking-tight bg-gradient-to-r from-[#00A896] via-[#0066FF] to-[#004BD6] bg-clip-text text-transparent">SATAK</h1>
                    <p class="text-[11px] font-semibold text-slate-400 tracking-widest">KONEK TERUS</p>
                </div>
                @if($errors->any())
                    <div class="mt-4 bg-red-50 text-red-600 text-xs px-3 py-2 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if(session('status'))
                    <div class="mt-4 bg-emerald-50 text-emerald-700 text-xs px-3 py-2 rounded border border-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif
                <form method="POST" action="/register" class="space-y-4" id="register-form">
                    @csrf
                    <div class="relative border-b-2 border-gray-200 focus-within:border-blue-600 transition-colors">
                        <span class="absolute left-0 top-3 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input type="text" name="name" placeholder="Nama" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none text-gray-900 font-medium placeholder-gray-500">
                    </div>
                    <div class="relative border-b-2 border-gray-200 focus-within:border-blue-600 transition-colors mt-4">
                        <span class="absolute left-0 top-3 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 018 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input type="email" name="email" placeholder="Email" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none text-gray-900 font-medium placeholder-gray-500">
                    </div>
                    <div class="relative border-b-2 border-gray-200 focus-within:border-blue-600 transition-colors mt-4">
                        <span class="absolute left-0 top-3 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a2 2 0 100-4 2 2 0 000 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 11V8a5 5 0 00-10 0v3"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 11h14v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8z"/></svg>
                        </span>
                        <input type="password" name="password" placeholder="Kata Sandi" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none text-gray-900 font-medium placeholder-gray-500">
                    </div>
                    <div class="relative border-b-2 border-gray-200 focus-within:border-blue-600 transition-colors mt-4">
                        <span class="absolute left-0 top-3 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a2 2 0 100-4 2 2 0 000 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 11V8a5 5 0 00-10 0v3"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 11h14v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8z"/></svg>
                        </span>
                        <input type="password" name="password_confirmation" placeholder="Ulangi Kata Sandi" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none text-gray-900 font-medium placeholder-gray-500">
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] hover:from-[#00B8A8] hover:via-[#0059C7] hover:to-[#0046B4] text-white text-sm font-bold py-3.5 rounded-xl shadow-md transition-all duration-200">Daftar</button>
                </form>
                <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">Sudah punya akun? <a href="/login" class="font-semibold text-blue-600 hover:underline">Masuk</a></p>
                    <p class="text-[11px] text-slate-400 mt-2">&copy; 2026 SATAK. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
