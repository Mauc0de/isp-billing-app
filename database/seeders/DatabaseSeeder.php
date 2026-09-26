<?php

namespace Database\Seeders;

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Buat Tenant utama
        $tenant = Tenant::create([
            'nama' => 'NetISP Indonesia',
            'kode' => 'NETISP',
            'alamat' => 'Jl. Teknologi No. 88, Jakarta Selatan',
            'telepon' => '021-77889900',
            'aktif' => true,
        ]);

        // User Admin
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Administrator',
            'email' => 'admin@netisp.id',
            'password' => Hash::make('admin123'),
        ]);

        // User biasa
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Petugas Billing',
            'email' => 'petugas@netisp.id',
            'password' => Hash::make('petugas123'),
        ]);

        // Paket Internet
        $paket1 = Paket::create([
            'tenant_id' => $tenant->id,
            'nama_paket' => 'Home 10 Mbps',
            'kecepatan' => '10 Mbps',
            'harga' => 150000,
            'deskripsi' => 'Paket internet rumahan 10 Mbps dengan kuota unlimited',
            'status' => 'aktif',
        ]);

        $paket2 = Paket::create([
            'tenant_id' => $tenant->id,
            'nama_paket' => 'Home 25 Mbps',
            'kecepatan' => '25 Mbps',
            'harga' => 250000,
            'deskripsi' => 'Paket internet rumahan 25 Mbps dengan kuota unlimited',
            'status' => 'aktif',
        ]);

        $paket3 = Paket::create([
            'tenant_id' => $tenant->id,
            'nama_paket' => 'Business 50 Mbps',
            'kecepatan' => '50 Mbps',
            'harga' => 500000,
            'deskripsi' => 'Paket internet bisnis 50 Mbps dengan IP publik statis',
            'status' => 'aktif',
        ]);

        $paket4 = Paket::create([
            'tenant_id' => $tenant->id,
            'nama_paket' => 'Business 100 Mbps',
            'kecepatan' => '100 Mbps',
            'harga' => 850000,
            'deskripsi' => 'Paket internet bisnis 100 Mbps dengan SLA 99.9%',
            'status' => 'aktif',
        ]);

        // Pelanggan
        $pelanggan1 = Pelanggan::create([
            'tenant_id' => $tenant->id,
            'nama' => 'Ahmad Rizki',
            'telepon' => '081234567890',
            'email' => 'ahmad.rizki@email.com',
            'alamat' => 'Jl. Merdeka No. 10, Jakarta Selatan',
            'paket_id' => $paket1->id,
            'status' => 'aktif',
            'tanggal_aktif' => '2026-08-01',
        ]);

        $pelanggan2 = Pelanggan::create([
            'tenant_id' => $tenant->id,
            'nama' => 'Siti Rahayu',
            'telepon' => '081234567891',
            'email' => 'siti.rahayu@email.com',
            'alamat' => 'Jl. Sudirman No. 25, Jakarta Pusat',
            'paket_id' => $paket2->id,
            'status' => 'aktif',
            'tanggal_aktif' => '2026-07-15',
        ]);

        $pelanggan3 = Pelanggan::create([
            'tenant_id' => $tenant->id,
            'nama' => 'Budi Santoso',
            'telepon' => '081234567892',
            'email' => 'budi.santoso@email.com',
            'alamat' => 'Jl. Thamrin No. 5, Jakarta Pusat',
            'paket_id' => $paket3->id,
            'status' => 'aktif',
            'tanggal_aktif' => '2026-06-20',
        ]);

        $pelanggan4 = Pelanggan::create([
            'tenant_id' => $tenant->id,
            'nama' => 'Dewi Lestari',
            'telepon' => '081234567893',
            'email' => 'dewi.lestari@email.com',
            'alamat' => 'Jl. Kuningan No. 15, Jakarta Selatan',
            'paket_id' => $paket4->id,
            'status' => 'aktif',
            'tanggal_aktif' => '2026-09-01',
        ]);

        $pelanggan5 = Pelanggan::create([
            'tenant_id' => $tenant->id,
            'nama' => 'PT Maju Bersama',
            'telepon' => '021-55667788',
            'email' => 'admin@majubersama.co.id',
            'alamat' => 'Jl. Rasuna Said No. 8, Jakarta Selatan',
            'paket_id' => $paket4->id,
            'status' => 'aktif',
            'tanggal_aktif' => '2026-05-10',
        ]);

        // Tagihan
        Tagihan::create([
            'tenant_id' => $tenant->id,
            'pelanggan_id' => $pelanggan1->id,
            'nomor_tagihan' => 'INV-2026-0001',
            'jumlah' => $paket1->harga,
            'tanggal_terbit' => '2026-09-01',
            'jatuh_tempo' => '2026-09-10',
            'status' => 'belum_bayar',
        ]);

        Tagihan::create([
            'tenant_id' => $tenant->id,
            'pelanggan_id' => $pelanggan2->id,
            'nomor_tagihan' => 'INV-2026-0002',
            'jumlah' => $paket2->harga,
            'tanggal_terbit' => '2026-09-01',
            'jatuh_tempo' => '2026-09-10',
            'status' => 'belum_bayar',
        ]);

        Tagihan::create([
            'tenant_id' => $tenant->id,
            'pelanggan_id' => $pelanggan3->id,
            'nomor_tagihan' => 'INV-2026-0003',
            'jumlah' => $paket3->harga,
            'tanggal_terbit' => '2026-08-01',
            'jatuh_tempo' => '2026-08-10',
            'tanggal_bayar' => '2026-08-08',
            'status' => 'lunas',
        ]);

        Tagihan::create([
            'tenant_id' => $tenant->id,
            'pelanggan_id' => $pelanggan4->id,
            'nomor_tagihan' => 'INV-2026-0004',
            'jumlah' => $paket4->harga,
            'tanggal_terbit' => '2026-09-01',
            'jatuh_tempo' => '2026-09-10',
            'status' => 'belum_bayar',
        ]);
    }
}
