@extends('layouts.app')

@section('title', 'Tagihan')

@section('content')
<div class="p-8">
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-lg">Daftar Tagihan</h3>
                <p class="text-gray-500 text-sm">Manajemen data tagihan</p>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Buat Tagihan Baru</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-left">ID</th>
                        <th class="p-4 text-left">Pelanggan</th>
                        <th class="p-4 text-left">Jumlah</th>
                        <th class="p-4 text-left">Jatuh Tempo</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="p-4">1</td>
                        <td class="p-4 font-medium">Ahmad</td>
                        <td class="p-4">Rp 150.000</td>
                        <td class="p-4">2025-12-01</td>
                        <td class="p-4"
                            ><span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">Belum Bayar</span></td>
                        <td class="p-4">
                            <button class="text-blue-600 mr-2">Edit</button>
                            <button class="text-red-600">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection