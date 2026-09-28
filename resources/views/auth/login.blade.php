<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATAK Keuangan - Masuk</title>
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

                <div class="flex p-1 bg-slate-100 rounded-full mb-6">
                    <button type="button" id="tab-admin" class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-full text-sm font-semibold transition bg-white shadow text-slate-900">Admin</button>
                    <button type="button" id="tab-pelanggan" class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-full text-sm font-semibold transition text-slate-500">Pelanggan</button>
                </div>

                <div class="bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] p-[1.5px] rounded-xl mb-6">
                    <div class="bg-white rounded-[10px] px-4 py-3">
                        <h2 id="head-title" class="text-sm font-bold text-slate-900">Masuk sebagai Admin</h2>
                        <p id="head-desc" class="text-xs text-slate-500">Kelola pelanggan, tagihan & laporan</p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="mb-4 bg-red-50 text-red-600 text-xs px-3 py-2.5 rounded-lg border border-red-200">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="/login" class="space-y-4" id="login-form">
                    @csrf
                    <input type="hidden" name="role" id="role-input" value="admin">
                    <div>
                        <label class="block text-[11px] font-bold tracking-wider text-slate-600 mb-2">EMAIL / USERNAME</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6"/></svg>
                            </span>
                            <input id="email-input" type="email" name="email" value="{{ old('email') }}" placeholder="admin@satak.net" required class="w-full pl-10 pr-4 py-3 text-sm border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 placeholder-slate-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold tracking-wider text-slate-600 mb-2">PASSWORD</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="10" rx="2"/><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="login_password" type="password" name="password" placeholder="••••••••" required class="w-full pl-10 pr-11 py-3 text-sm border border-slate-200 rounded-xl outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 placeholder-slate-400">
                            <button type="button" onclick="document.getElementById('login_password').type=document.getElementById('login_password').type==='password'?'text':'password'" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <a href="#" class="text-xs font-medium text-blue-600 hover:underline">Lupa Password?</a>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white text-sm font-bold py-3.5 rounded-xl shadow-md">Masuk</button>
                </form>

                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">Belum punya akun? <a href="/register" class="font-semibold text-blue-600 hover:underline">Daftar</a></p>
                    <p class="text-[11px] text-slate-400 mt-2">&copy; 2026 SATAK. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        const tabAdmin=document.getElementById('tab-admin');
        const tabPel=document.getElementById('tab-pelanggan');
        const roleInput=document.getElementById('role-input');
        const headTitle=document.getElementById('head-title');
        const headDesc=document.getElementById('head-desc');
        const emailInput=document.getElementById('email-input');
        function setRole(r){
            roleInput.value=r;
            if(r==='admin'){
                tabAdmin.className='flex-1 flex items-center justify-center gap-2 py-2.5 rounded-full text-sm font-semibold transition bg-white shadow text-slate-900';
                tabPel.className='flex-1 flex items-center justify-center gap-2 py-2.5 rounded-full text-sm font-semibold transition text-slate-500';
                headTitle.textContent='Masuk sebagai Admin';
                headDesc.textContent='Kelola pelanggan, tagihan & laporan';
                emailInput.placeholder='admin@satak.net';
            } else {
                tabPel.className='flex-1 flex items-center justify-center gap-2 py-2.5 rounded-full text-sm font-semibold transition bg-white shadow text-slate-900';
                tabAdmin.className='flex-1 flex items-center justify-center gap-2 py-2.5 rounded-full text-sm font-semibold transition text-slate-500';
                headTitle.textContent='Masuk sebagai Pelanggan';
                headDesc.textContent='Lihat tagihan & riwayat pembayaran';
                emailInput.placeholder='pelanggan@email.com';
            }
        }
        tabAdmin.addEventListener('click',()=>setRole('admin'));
        tabPel.addEventListener('click',()=>setRole('pelanggan'));
    </script>
</body>
</html>
