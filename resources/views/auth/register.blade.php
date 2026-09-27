<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SATAK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative">
    <div class="absolute inset-0" style="background-image:url('/latar.jpeg'); background-size:cover; background-position:center; filter:blur(2px);"></div>
    <div class="absolute inset-0 bg-[#0a2540]/60"></div>
    <div class="relative bg-white/85 backdrop-blur-2xl rounded-2xl shadow-2xl w-full max-w-[370px] px-8 pt-8 pb-10 z-10 border border-white/60">
        <div class="flex flex-col items-center">
            <div class="w-20 h-20 rounded-full bg-blue-50 border-2 border-blue-100 flex items-center justify-center overflow-hidden shadow-inner">
                <svg viewBox="0 0 100 100" class="w-16 h-16">
                    <circle cx="50" cy="50" r="50" fill="#eff6ff"/>
                    <ellipse cx="50" cy="78" rx="26" ry="12" fill="#2563eb"/>
                    <circle cx="50" cy="42" r="22" fill="#ffcc9e"/>
                    <path d="M28 38 C28 18 42 12 50 14 C68 16 74 28 72 42 C68 38 62 32 56 30 C56 30 53 18 38 22 C32 24 28 30 28 38Z" fill="#1e293b"/>
                    <circle cx="62" cy="20" r="7" fill="#1e293b"/>
                </svg>
            </div>
            <h1 class="mt-3 text-[20px] font-black tracking-wider text-gray-900">CREATE ACCOUNT</h1>
            <p class="text-xs text-blue-900/70 font-medium">Sistem Keuangan ISP</p>
        </div>

        @if($errors->any())
            <div class="mt-4 bg-red-50 text-red-600 text-xs px-3 py-2 rounded">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/register" class="mt-6">
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

            <button type="submit" class="mt-6 w-full bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-bold tracking-wider py-3.5 rounded-xl shadow-lg shadow-blue-700/30 hover:shadow-blue-700/50 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">DAFTAR</button>
        </form>

        <p class="mt-6 text-center text-xs text-gray-500">Sudah punya akun? <a href="/login" class="text-blue-600 hover:text-blue-800 hover:underline font-semibold">Masuk</a></p>
        <p class="mt-4 text-center text-xs text-gray-500">&copy; 2026 SATAK. All rights reserved.</p>
    </div>
</body>
</html>
