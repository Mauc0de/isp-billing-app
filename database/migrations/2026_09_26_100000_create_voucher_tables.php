<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Voucher hotspot/prepaid ala PHPNuxBill.
 *
 * Satu voucher mewakili satu kupon berisi kode, paket, dan masa aktif.
 * Dikelompokkan per batch (batch_vouchers) supaya hasil generate bisa
 * dicetak/diarsipkan bersama, tanpa mengubah data paket yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('paket_id')->nullable()->constrained('pakets')->nullOnDelete();
            $table->string('nama', 100);
            $table->string('kode_prefix', 20)->nullable();
            $table->unsignedInteger('jumlah')->default(0);
            $table->unsignedInteger('panjang_kode')->default(8);
            $table->unsignedInteger('masa_aktif_hari')->default(30);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at'], 'voucher_batches_tenant_created_idx');
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('batch_id')->nullable()->constrained('voucher_batches')->cascadeOnDelete();
            $table->foreignUlid('paket_id')->nullable()->constrained('pakets')->nullOnDelete();
            $table->string('kode', 32)->unique();
            $table->unsignedInteger('harga')->default(0);
            $table->unsignedInteger('masa_aktif_hari')->default(30);
            $table->string('status', 20)->default('belum_dipakai');
            $table->foreignUlid('pelanggan_id')->nullable()->constrained('pelanggans')->nullOnDelete();
            $table->timestamp('dipakai_at')->nullable();
            $table->timestamp('kadaluarsa_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'vouchers_tenant_status_idx');
            $table->index(['tenant_id', 'batch_id'], 'vouchers_tenant_batch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('voucher_batches');
    }
};
