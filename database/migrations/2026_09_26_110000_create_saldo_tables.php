<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saldo pelanggan + riwayat mutasi.
 *
 * Prinsip ala PHPNuxBill: saldo disimpan sebagai single source of truth di
 * pelanggans.saldo, sementara setiap perubahan dicatat di saldo_mutations
 * sebagai audit trail. Auto-renew mengambil saldo untuk melunasi tagihan yang
 * jatuh tempo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->integer('saldo')->default(0)->after('status');
            $table->boolean('auto_renew')->default(false)->after('saldo');
        });

        Schema::create('saldo_mutations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->string('jenis', 10);
            $table->integer('jumlah');
            $table->integer('saldo_akhir');
            $table->string('sumber', 30);
            $table->string('referensi', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'pelanggan_id', 'created_at'], 'saldo_mutations_tenant_pelanggan_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_mutations');

        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropColumn(['saldo', 'auto_renew']);
        });
    }
};
