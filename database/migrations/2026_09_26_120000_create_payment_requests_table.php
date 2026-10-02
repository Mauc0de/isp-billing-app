<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permintaan pembayaran dari pelanggan (top-up saldo atau bayar tagihan).
 *
 * Ini fondasi payment gateway "manual transfer": pelanggan mengunggah bukti
 * transfer, admin memverifikasi, lalu saldo/tagihan diperbarui. Kolom
 * provider/checkout_url disediakan agar adapter gateway online (Tripay,
 * Midtrans, Xendit) bisa dipasang tanpa mengubah skema lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignUlid('tagihan_id')->nullable()->constrained('tagihans')->nullOnDelete();
            $table->string('tujuan', 20);
            $table->integer('jumlah');
            $table->string('metode', 30)->default('transfer_manual');
            $table->string('status', 20)->default('menunggu');
            $table->string('provider', 30)->nullable();
            $table->string('provider_reference', 100)->nullable();
            $table->string('checkout_url', 500)->nullable();
            $table->string('bukti_path')->nullable();
            $table->text('catatan_pelanggan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignUlid('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'payment_requests_tenant_status_idx');
            $table->index(['tenant_id', 'pelanggan_id'], 'payment_requests_tenant_pelanggan_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
