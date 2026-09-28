<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tagihan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pelanggan_id')->constrained()->cascadeOnDelete();
            $table->integer('jumlah')->default(0);
            $table->date('tanggal_bayar');
            $table->string('metode_pembayaran', 50)->default('tunai');
            $table->string('referensi', 100)->nullable();
            $table->enum('status', ['berhasil', 'pending', 'gagal'])->default('berhasil');
            $table->text('keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'tagihan_id', 'pelanggan_id', 'jumlah', 'tanggal_bayar', 'metode_pembayaran', 'referensi', 'status', 'keterangan']);
        });
    }
};
