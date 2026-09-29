<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel inti billing dengan penamaan Indonesia.
     *
     * Menggantikan 8 migration terpisah yang tidak konsisten:
     * - ke-4 file "create_*_table" hanya membuat id + timestamps
     * - ke-4 file "add_columns_to_*" menambahkan kolom dengan foreignId (bigint)
     *   padahal tabel utama repo memakai ULID varchar, sehingga tenant_id
     *   tidak akan pernah cocok dengan ULID yang disimpan di session.
     *
     * Semua id mengikuti konvensi ULID yang sudah dipakai create_users_table
     * dan create_billing_core_tables.
     */
    public function up(): void
    {
        Schema::create('pakets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nama_paket');
            $table->string('kecepatan', 50)->nullable();
            $table->integer('harga')->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'pakets_tenant_status_idx');
        });

        Schema::create('pelanggans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('paket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->string('telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('status', 20)->default('aktif');
            $table->date('tanggal_aktif')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'pelanggans_tenant_status_idx');
        });

        Schema::create('tagihans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('pelanggan_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_tagihan', 50)->unique();
            $table->integer('jumlah')->default(0);
            $table->date('tanggal_terbit');
            $table->date('jatuh_tempo');
            $table->date('tanggal_bayar')->nullable();
            $table->string('status', 30)->default('belum_bayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'tagihans_tenant_status_idx');
        });

        Schema::create('pembayarans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('tagihan_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('pelanggan_id')->constrained()->cascadeOnDelete();
            $table->integer('jumlah')->default(0);
            $table->date('tanggal_bayar');
            $table->string('metode_pembayaran', 50)->default('tunai');
            $table->string('referensi', 100)->nullable();
            $table->string('status', 20)->default('berhasil');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'tanggal_bayar'], 'pembayarans_tenant_tanggal_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('tagihans');
        Schema::dropIfExists('pelanggans');
        Schema::dropIfExists('pakets');
    }
};
