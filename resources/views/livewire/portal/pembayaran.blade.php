<div class="bg-[#F1F5F9] text-slate-800 min-h-screen">
    <x-portal-sidebar />
    <!-- Main Layout -->
    <main class="md:ml-60 flex flex-col min-h-screen">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8 sticky top-0 z-20">
            <div>
                <h1 class="text-lg font-bold text-slate-900 leading-tight">Pembayaran</h1>
                <p class="text-[11px] text-slate-400 font-medium">Riwayat pembayaran kamu</p>
            </div>
        </header>

        <div class="flex-1 p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="font-bold text-lg text-slate-900">Riwayat Pembayaran</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Semua pembayaran yang sudah kamu lakukan</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[11px] text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100">
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3">Metode</th>
                                    <th class="px-6 py-3">Jumlah</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pembayarans as $pembayaran)
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition">
                                        <td class="px-6 py-4 font-semibold text-[13px] text-slate-900">{{ $pembayaran->tanggal_bayar?->format('d M Y') }}</td>
                                        <td class="px-6 py-4 text-[13px] text-slate-500">{{ $pembayaran->metode_pembayaran }}</td>
                                        <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            @if($pembayaran->status === 'berhasil')
                                                <span class="bg-[#F0FDF4] text-[#059669] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#D1FAE5]">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                                                    Berhasil
                                                </span>
                                            @elseif($pembayaran->status === 'pending')
                                                <span class="bg-[#FFFBEB] text-[#D97706] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#FEF3C7]">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></div>
                                                    Pending
                                                </span>
                                            @else
                                                <span class="bg-[#FEF2F2] text-[#DC2626] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#FECACA]">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></div>
                                                    Gagal
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">Belum ada pembayaran</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-100">
                        {{ $pembayarans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
