@extends('layouts.app')

@section('title', 'Router')

@section('content')
<x-page-header title="Router" subtitle="Kelola router Mikrotik" />

<div class="p-4 md:p-8">
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <x-card class="mb-6">
        <x-slot:title>Tambah Router</x-slot:title>
        <form method="POST" action="{{ route('router.store') }}" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
            @csrf
            <div><label class="label">Nama</label><input name="name" class="input" required></div>
            <div><label class="label">Lokasi</label><input name="location" class="input"></div>
            <div><label class="label">IP / VPN</label><input name="vpn_ip" class="input" required placeholder="10.10.10.1"></div>
            <div><label class="label">Port API</label><input name="api_port" type="number" value="8728" class="input"></div>
            <div><label class="label">Username</label><input name="username" class="input" required></div>
            <div><label class="label">Password</label><input name="password" type="password" class="input" required></div>
            <div>
                <label class="label">Metode Suspend</label>
                <select name="suspend_method" class="input">
                    <option value="ppp_secret">Disable PPP secret</option>
                    <option value="address_list">Blokir di address-list</option>
                </select>
            </div>
            <div><label class="label">Nama address-list blokir</label><input name="address_list_name" class="input" placeholder="satak-blocklist"></div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" name="use_ssl" value="1" id="ssl">
                <label for="ssl" class="text-sm">Gunakan SSL</label>
            </div>
            <div class="md:col-span-3"><button class="btn-primary">Tambah Router</button></div>
        </form>
    </x-card>

    <x-card>
        <x-slot:title>Daftar Router</x-slot:title>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="p-4 font-bold">Nama</th>
                        <th class="p-4 font-bold">IP</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold">Aktif</th>
                        <th class="p-4 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routers as $r)
                    <tr class="border-t border-slate-100">
                        <td class="p-4 font-medium">{{ $r->name }} <span class="text-slate-400">· {{ $r->location }}</span></td>
                        <td class="p-4 font-mono text-slate-500">{{ $r->vpn_ip }}:{{ $r->api_port }}</td>
                        <td class="p-4">
                            <x-badge :tone="$r->status?->value === 'online' ? 'emerald' : ($r->status?->value === 'offline' ? 'rose' : 'slate')">
                                {{ $r->status?->value ?? 'unknown' }}
                            </x-badge>
                        </td>
                        <td class="p-4">{{ $r->is_active ? 'Ya' : 'Tidak' }}</td>
                        <td class="p-4">
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('router.test', $r) }}">@csrf<button class="text-xs font-semibold text-brand-600 hover:underline">Test</button></form>
                                <form method="POST" action="{{ route('router.toggle', $r) }}">@csrf<button class="text-xs font-semibold text-amber-600 hover:underline">{{ $r->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                                <form method="POST" action="{{ route('router.destroy', $r) }}" onsubmit="return confirm('Arsipkan router ini?')">@csrf @method('DELETE')<button class="text-xs font-semibold text-rose-600 hover:underline">Arsipkan</button></form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada router</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
