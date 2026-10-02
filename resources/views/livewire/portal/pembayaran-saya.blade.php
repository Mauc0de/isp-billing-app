<div class="bg-[#F1F5F9] text-slate-800 min-h-screen">
    <!-- Sidebar -->
    <aside class="w-60 bg-white flex-col h-full border-r border-slate-200 shrink-0 hidden md:flex fixed left-0 top-0 z-30">
        <div class="h-16 flex items-center px-5 gap-3 border-b border-slate-100">
            <div class="w-9 h-9 bg-[#2563EB] rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
            </div>
            <div>
                <p class="font-extrabold text-slate-900 text-[15px] leading-tight tracking-wide">SATAK</p>
                <p class="text-[11px] text-slate-400 font-medium">Solusi Internet</p>
            </div>
        </div>
        <div class="px-4 py-5 flex-1">
            <p class="px-3 text-[10px] font-bold text-slate-400 mb-3 tracking-widest uppercase">Menu</p>
            <nav class="space-y-1">
                <a href="/portal" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">Dashboard</a>
                <a href="/portal/tagihan" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">Tagihan Saya</a>
                <a href="/portal/pembayaran" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">Pembayaran</a>
                <a href="/portal/pembayaran-saya" class="flex items-center gap-3 bg-[#2563EB] text-white px-3 py-2.5 rounded-xl font-semibold text-sm shadow-sm">Bayar / Top-up</a>
                <a href="/portal/paket" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">Paket Internet</a>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-100">
            <a href="/logout" onclick="event.preventDefault(); document.getElementById('portal-logout').submit();" class="flex items-center gap-3 bg-slate-900 hover:bg-slate-800 text-white px-3 py-2.5 rounded-xl font-semibold text-sm transition">Keluar</a>
            <form id="portal-logout" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
        </div>
    </aside>

    <main class="md:ml-60 flex flex-col min-h-screen">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8 sticky top-0 z-20">
            <div>
                <h1 class="text-lg font-bold text-slate-900 leading-tight">Bayar / Top-up</h1>
                <p class="text-[11px] text-slate-400 font-medium">Transfer manual, unggah bukti, tunggu verifikasi admin</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-400">Saldo</p>
                <p class="text-sm font-bold text-slate-900">Rp {{ number_format($pelanggan?->saldo ?? 0, 0, ',', '.') }}</p>
            </div>
        </header>

        <div class="flex-1 p-6 lg:p-8">
            <div class="max-w-5xl mx-auto space-y-6 pb-8">

                @if(session('portal_status'))
                    <div class="bg-emerald-50 text-emerald-700 text-sm px-4 py-3 rounded-xl border border-emerald-200">{{ session('portal_status') }}</div>
                @endif
                @if($errors->any())
                    <div class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-xl border border-red-200 space-y-1">
                        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    {{-- Instruksi transfer --}}
                    <div class="bg-gradient-to-br from-[#1D4ED8] to-[#2563EB] rounded-2xl p-6 text-white">
                        <h2 class="font-bold text-lg">Transfer ke Rekening</h2>
                        <p class="text-blue-100 text-xs mt-1">Setelah transfer, unggah bukti di form sebelah.</p>
                        <div class="mt-5 bg-white/10 rounded-xl p-4">
                            <p class="text-xs text-white/70">Bank</p>
                            <p class="font-bold text-lg">{{ $bank['nama'] }}</p>
                            <p class="mt-3 text-xs text-white/70">Nomor Rekening</p>
                            <p class="font-mono font-bold text-xl tracking-wider">{{ $bank['nomor'] }}</p>
                            <p class="mt-3 text-xs text-white/70">Atas Nama</p>
                            <p class="font-semibold">{{ $bank['atas_nama'] }}</p>
                        </div>
                    </div>

                    {{-- Form pengajuan --}}
                    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-6">
                        <h2 class="font-bold text-lg text-slate-900">Ajukan Pembayaran</h2>
                        <form wire:submit="ajukan" class="mt-4 space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tujuan</label>
                                <select wire:model.live="tujuan" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 bg-white outline-none">
                                    <option value="topup_saldo">Top-up saldo</option>
                                    <option value="bayar_tagihan">Bayar tagihan</option>
                                </select>
                            </div>

                            @if($tujuan === 'bayar_tagihan')
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tagihan</label>
                                <select wire:model.live="tagihanId" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 bg-white outline-none">
                                    <option value="">— Pilih tagihan —</option>
                                    @foreach($tagihans as $tagihan)
                                        <option value="{{ $tagihan->id }}">{{ $tagihan->nomor_tagihan }} — Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Transfer (Rp)</label>
                                <input type="number" wire:model="jumlah" min="1000" step="1000"
                                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Bukti Transfer (gambar)</label>
                                <input type="file" wire:model="bukti" accept="image/*"
                                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none">
                                @error('bukti') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                <div wire:loading wire:target="bukti" class="text-xs text-slate-400 mt-1">Mengunggah…</div>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white text-sm font-semibold py-3 rounded-xl shadow-md">
                                Kirim Pembayaran
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Riwayat pengajuan --}}
                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="font-bold text-lg text-slate-900">Riwayat Pengajuan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[11px] text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100">
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3">Tujuan</th>
                                    <th class="px-6 py-3">Jumlah</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat as $item)
                                <tr class="border-b border-slate-50">
                                    <td class="px-6 py-4 text-[13px] text-slate-500">{{ $item->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-[13px] text-slate-700">{{ $item->tujuan->label() }}</td>
                                    <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        @php($color = match($item->status->value) {
                                            'menunggu' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'terverifikasi' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            default => 'bg-rose-50 text-rose-600 border-rose-200',
                                        })
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $color }}">{{ $item->status->label() }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">Belum ada pengajuan</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-100">{{ $riwayat->links() }}</div>
                </div>
            </div>
        </div>
    </main>
</div>
