<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SATAK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#2fa9e2] min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-[360px] px-8 pt-8 pb-10">
        <div class="flex flex-col items-center">
            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                <svg viewBox="0 0 100 100" class="w-16 h-16">
                    <circle cx="50" cy="50" r="50" fill="#f3f4f6"/>
                    <ellipse cx="50" cy="78" rx="26" ry="12" fill="#14b8a6"/>
                    <circle cx="50" cy="42" r="22" fill="#ffcc9e"/>
                    <path d="M28 38 C28 18 42 12 50 14 C68 16 74 28 72 42 C68 38 62 32 56 30 C56 30 53 18 38 22 C32 24 28 30 28 38Z" fill="#111827"/>
                    <circle cx="62" cy="20" r="7" fill="#111827"/>
                </svg>
            </div>
            <h1 class="mt-3 text-[18px] font-bold tracking-wide text-gray-900">Create Account</h1>
        </div>

        @if($errors->any())
            <div class="mt-4 bg-red-50 text-red-600 text-xs px-3 py-2 rounded space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/register" class="mt-6">
            @csrf
            <div class="relative border-b border-gray-200 focus-within:border-teal-500">
                <span class="absolute left-0 top-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1M9 13h1m4 0h1M9 17h1m4 0h1"/></svg>
                </span>
                <input type="text" name="tenant_slug" value="{{ old('tenant_slug') }}" placeholder="Kode ISP" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none placeholder-gray-400">
                <p class="text-[11px] text-gray-400 mt-1 pl-7">Slug tenant dari admin jaringan Anda, mis. <span class="font-mono">demo-isp</span>.</p>
            </div>

            <div class="relative border-b border-gray-200 focus-within:border-teal-500 mt-4">
                <span class="absolute left-0 top-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none placeholder-gray-400">
            </div>

            <div class="relative border-b border-gray-200 focus-within:border-teal-500 mt-4">
                <span class="absolute left-0 top-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 018 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none placeholder-gray-400">
            </div>

            <div class="relative border-b border-gray-200 focus-within:border-teal-500 mt-4">
                <span class="absolute left-0 top-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a2 2 0 100-4 2 2 0 000 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 11V8a5 5 0 00-10 0v3"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 11h14v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8z"/></svg>
                </span>
                <input type="password" name="password" placeholder="Kata Sandi" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none placeholder-gray-400">
            </div>

            <div class="relative border-b border-gray-200 focus-within:border-teal-500 mt-4">
                <span class="absolute left-0 top-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a2 2 0 100-4 2 2 0 000 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 11V8a5 5 0 00-10 0v3"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 11h14v8a5 5 0 01-2 2H7a5 5 0 01-2-2v-8z"/></svg>
                </span>
                <input type="password" name="password_confirmation" placeholder="Ulangi Kata Sandi" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none placeholder-gray-400">
            </div>

            <button type="submit" class="mt-6 w-full bg-[#1abc9c] hover:bg-[#16a085] text-white text-sm font-semibold tracking-wide py-3 rounded-full shadow-md transition">Daftar</button>
        </form>

        <p class="mt-6 text-center text-xs text-gray-400">Sudah punya akun? <a href="/login" class="text-[#1abc9c] hover:underline font-medium">Masuk</a></p>
    </div>
</body>
</html>
