@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
<div class="p-8">
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-lg">Daftar Pelanggan</h3>
                <p class="text-gray-500 text-sm">Manajemen data pelanggan</p>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Tambah Pelanggan</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-left">ID</th>
                        <th class="p-4 text-left">Nama</th>
                        <th class="p-4 text-left">Telepon</th>
                        <th class="p-4 text-left">Email</th>
                        <th class="p-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t">
                        <td class="p-4">1</td>
                        <td class="p-4 font-medium">Ahmad</td>
                        <td class="p-4">0812345678</td>
                        <td class="p-4">ahmad@example.com</td>
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