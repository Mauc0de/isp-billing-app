<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SATAK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative">
    <!-- blurred background image -->
    <div class="absolute inset-0" style="background-image:url('/latar.jpeg'); background-size:cover; background-position:center; filter:blur(2px);"></div>
    <!-- dark overlay for contrast -->
    <div class="absolute inset-0 bg-[#0d3b66] opacity-30"></div>
    <!-- login card -->
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-[360px] px-8 pt-8 pb-10 z-10">
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
            <h1 class="mt-3 text-[18px] font-bold tracking-wide text-gray-900">WELCOME</h1>
        </div>

        @if($errors->any())
            <div class="mt-4 bg-red-50 text-red-600 text-xs px-3 py-2 rounded">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/login" class="mt-6">
            @csrf
            <div class="relative border-b border-gray-200 focus-within:border-teal-500">
                <span class="absolute left-0 top-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input type="text" name="email" value="{{ old('email') }}" placeholder="Username" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none placeholder-gray-400">
            </div>

            <div class="relative border-b border-gray-200 focus-within:border-teal-500 mt-4">
                <span class="absolute left-0 top-3 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a2 2 0 100-4 2 2 0 000 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 11V8a5 5 0 00-10 0v3"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 11h14v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8z"/></svg>
                </span>
                <input type="password" name="password" placeholder="Password" required class="w-full pl-7 pr-2 py-2.5 text-sm bg-transparent outline-none placeholder-gray-400">
            </div>

            <div class="text-right mt-2">
                <a href="#" class="text-[11px] text-gray-400 hover:text-gray-600">Forgot password?</a>
            </div>

            <button type="submit" class="mt-6 w-full bg-[#1abc9c] hover:bg-[#16a085] text-white text-sm font-semibold tracking-wide py-3 rounded-full shadow-md transition">LOGIN</button>
        </form>

        <p class="mt-6 text-center text-xs text-gray-400">Belum punya akun? <a href="/register" class="text-[#1abc9c] hover:underline font-medium">Daftar</a></p>
    </div>
</body>
</html>