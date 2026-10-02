@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Pengaturan</h2>
        <p class="text-gray-500 text-xs md:text-sm">Konfigurasi billing, notifikasi WhatsApp, dan rekening pembayaran</p>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6 max-w-5xl">

    @if(session('status'))
        <div class="bg-emerald-50 text-emerald-700 text-sm px-4 py-3 rounded-xl border border-emerald-200">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-xl border border-red-200 space-y-1">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @can('settings.update')
    <form method="POST" action="{{ route('pengaturan.update') }}" class="space-y-6">
        @csrf
    @endcan

        {{-- Billing --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900">Billing &amp; Suspensi</h3>
                <p class="text-xs text-slate-400 mt-0.5">Mengatur kapan tagihan dianggap terlambat dan kapan pengingat dikirim.</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Masa Tenggang Suspend (hari)</label>
                    <input type="number" name="grace_period_days" value="{{ old('grace_period_days', $gracePeriodDays) }}" min="0" max="90" required
                           @cannot('settings.update') disabled @endcannot
                           class="input">
                    <p class="text-xs text-slate-400 mt-1">Pelanggan disuspend setelah lewat jatuh tempo sekian hari.</p>
                </div>
                <div>
                    <label class="label">Pengingat Jatuh Tempo (hari sebelum)</label>
                    <input type="number" name="reminder_days_before" value="{{ old('reminder_days_before', $reminderDaysBefore) }}" min="0" max="30" required
                           @cannot('settings.update') disabled @endcannot
                           class="input">
                    <p class="text-xs text-slate-400 mt-1">Pengingat WhatsApp dikirim sekian hari sebelum jatuh tempo.</p>
                </div>
            </div>
        </div>

        {{-- WhatsApp --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900">Notifikasi WhatsApp</h3>
                <p class="text-xs text-slate-400 mt-0.5">Token API tetap dibaca dari file .env (tidak disimpan di sini demi keamanan).</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="md:w-1/2">
                    <label class="label">Provider</label>
                    <select name="whatsapp_provider" @cannot('settings.update') disabled @endcannot
                            class="input">
                        @foreach($providers as $provider)
                            <option value="{{ $provider->value }}" @selected(old('whatsapp_provider', $whatsappProvider) === $provider->value)>
                                {{ ucfirst($provider->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <p class="text-xs text-slate-500 bg-slate-50 rounded-lg p-3">
                    Placeholder tersedia: <span class="font-mono">{{ $placeholders }}</span>
                </p>

                <div>
                    <label class="label">Template Suspend</label>
                    <textarea name="template_suspend" rows="3" @cannot('settings.update') disabled @endcannot
                              class="input">{{ old('template_suspend', $templates['suspend']) }}</textarea>
                </div>
                <div>
                    <label class="label">Template Reaktivasi</label>
                    <textarea name="template_reactivate" rows="3" @cannot('settings.update') disabled @endcannot
                              class="input">{{ old('template_reactivate', $templates['reactivate']) }}</textarea>
                </div>
                <div>
                    <label class="label">Template Pengingat Jatuh Tempo</label>
                    <textarea name="template_due_reminder" rows="3" @cannot('settings.update') disabled @endcannot
                              class="input">{{ old('template_due_reminder', $templates['due_reminder']) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Rekening --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900">Rekening Pembayaran</h3>
                <p class="text-xs text-slate-400 mt-0.5">Ditampilkan di portal pelanggan saat mengajukan transfer manual.</p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label">Nama Bank</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $bank['nama']) }}" maxlength="100" required
                           @cannot('settings.update') disabled @endcannot
                           class="input">
                </div>
                <div>
                    <label class="label">Nomor Rekening</label>
                    <input type="text" name="bank_number" value="{{ old('bank_number', $bank['nomor']) }}" maxlength="50" required
                           @cannot('settings.update') disabled @endcannot
                           class="input">
                </div>
                <div>
                    <label class="label">Atas Nama</label>
                    <input type="text" name="bank_holder" value="{{ old('bank_holder', $bank['atas_nama']) }}" maxlength="100" required
                           @cannot('settings.update') disabled @endcannot
                           class="input">
                </div>
            </div>
        </div>

        @can('settings.update')
        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                Simpan Pengaturan
            </button>
        </div>
        @endcan

    @can('settings.update')
    </form>
    @endcan

    @cannot('settings.update')
    <div class="bg-amber-50 text-amber-700 text-sm px-4 py-3 rounded-xl border border-amber-200">
        Anda hanya dapat melihat pengaturan. Hubungi administrator untuk mengubahnya.
    </div>
    @endcannot
</div>
@endsection
