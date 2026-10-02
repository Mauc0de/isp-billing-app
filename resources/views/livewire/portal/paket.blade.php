<div class="bg-[#F1F5F9] text-slate-800 min-h-screen">
    <x-portal-sidebar />
    <!-- Main Layout -->
    <main class="md:ml-60 flex flex-col min-h-screen">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8 sticky top-0 z-20">
            <div>
                <h1 class="text-lg font-bold text-slate-900 leading-tight">Paket Internet</h1>
                <p class="text-[11px] text-slate-400 font-medium">Detail paket langganan kamu</p>
            </div>
        </header>

        <div class="flex-1 p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @if($paket)
                    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center">
                                <svg class="w-8 h-8 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-slate-900">{{ $paket->nama_paket }}</h2>
                                <p class="text-sm text-slate-500">{{ $paket->deskripsi }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-slate-50 rounded-xl p-5">
                                <p class="text-xs font-medium text-slate-400">Kecepatan</p>
                                <p class="text-xl font-bold text-slate-900 mt-1">{{ $paket->kecepatan }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-5">
                                <p class="text-xs font-medium text-slate-400">Harga per Bulan</p>
                                <p class="text-xl font-bold text-slate-900 mt-1">Rp {{ number_format($paket->harga, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-5">
                                <p class="text-xs font-medium text-slate-400">Status</p>
                                <p class="text-xl font-bold text-emerald-600 mt-1">{{ ucfirst($paket->status) }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-8 text-center">
                        <p class="text-slate-400">Tidak ada paket aktif</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
