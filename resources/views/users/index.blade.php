@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
        <p class="text-gray-500 text-xs md:text-sm">Kelola akun staf, role, status aktif, dan password</p>
    </div>
    <div class="text-xs text-gray-500">
        {{ $users->count() }} pengguna
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 text-emerald-700 text-sm px-4 py-3 rounded-xl border border-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-xl border border-red-200 space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Form Tambah Pengguna --}}
    @can('users.create')
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Tambah Pengguna Baru</h3>
            <p class="text-xs text-slate-400 mt-0.5">Akun langsung aktif dan dapat login.</p>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password (min. 8)</label>
                <input type="password" name="password" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Role</label>
                <select name="role" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none bg-white">
                    <option value="">— Tanpa role —</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" @selected(old('role') === $role->slug)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <button type="submit" class="bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md">
                    Tambah Pengguna
                </button>
            </div>
        </form>
    </div>
    @endcan

    {{-- Daftar Pengguna --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Nama</th>
                        <th class="text-left p-4 text-gray-500">Email</th>
                        <th class="text-left p-4 text-gray-500">Role</th>
                        <th class="text-left p-4 text-gray-500">Status</th>
                        <th class="text-left p-4 text-gray-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="border-t align-top">
                        <td class="p-4 font-medium">{{ $user->name }}</td>
                        <td class="p-4 text-gray-500">{{ $user->email }}</td>
                        <td class="p-4">
                            @can('users.update')
                            <form method="POST" action="{{ route('users.role', $user) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="role" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-white outline-none">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->slug }}" @selected($user->roles->contains('slug', $role->slug))>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-xs font-semibold text-blue-600 hover:underline">Simpan</button>
                            </form>
                            @else
                                {{ $user->roles->pluck('name')->join(', ') ?: '—' }}
                            @endcan
                        </td>
                        <td class="p-4">
                            @if($user->is_active)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col items-end gap-2">
                                @can('users.update')
                                <form method="POST" action="{{ route('users.status', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-xs font-semibold px-3 py-1.5 rounded-lg border {{ $user->is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}"
                                            @disabled($user->id === auth()->id())>
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                @endcan

                                @can('users.update')
                                <details class="w-full max-w-[240px]">
                                    <summary class="text-xs font-semibold text-slate-500 cursor-pointer hover:text-slate-800 text-right">Reset password</summary>
                                    <form method="POST" action="{{ route('users.password', $user) }}" class="mt-2 flex flex-col gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="password" name="password" placeholder="Password baru" required minlength="8"
                                               class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 outline-none">
                                        <input type="password" name="password_confirmation" placeholder="Ulangi password" required minlength="8"
                                               class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 outline-none">
                                        <button type="submit" class="text-xs font-semibold text-blue-600 hover:underline text-right">Simpan password</button>
                                    </form>
                                </details>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">Belum ada pengguna</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
