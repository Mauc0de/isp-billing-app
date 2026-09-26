@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<div class="p-8">
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-lg">Daftar Pembayaran</h3>
                <p class="text-gray-500 text-sm">Manajemen data pembayaran</p>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Tambah Pembayaran</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-left">ID</th>
                        <th class="p-4 text-left">Pelanggan</th>
                        <th class="p-4 text-left">Paket</th>
                        <th class="p-4 text-left">Jumlah</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="p-4">1</td>
                        <td class="p-4 font-medium">Ahmad</td>
                        <td class="p-4 text-gray-500">10 Mbps</td>
                        <td class="p-4">Rp 150.000</td>
                        <td class="p-4"><span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">Lunas</span></td>
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