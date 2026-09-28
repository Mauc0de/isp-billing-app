<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pelanggan_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_tagihan', 50)->unique();
            $table->integer('jumlah')->default(0);
            $table->date('tanggal_terbit');
            $table->date('jatuh_tempo');
            $table->date('tanggal_bayar')->nullable();
            $table->enum('status', ['belum_bayar', 'lunas', 'lewat_jatuh_tempo', 'dibatalkan'])->default('belum_bayar');
            $table->text('keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'pelanggan_id', 'nomor_tagihan', 'jumlah', 'tanggal_terbit', 'jatuh_tempo', 'tanggal_bayar', 'status', 'keterangan']);
        });
    }
};
