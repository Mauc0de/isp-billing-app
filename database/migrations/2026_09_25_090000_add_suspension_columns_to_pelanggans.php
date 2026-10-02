<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom tambahan yang dibutuhkan pipeline automasi suspend & notifikasi.
 *
 * Skema billing tim memakai penamaan Indonesia (pelanggans, tagihans) dan
 * belum menyimpan sumber suspensi, alasan, nomor pelanggan, maupun nomor
 * WhatsApp. Migration ini melengkapinya supaya OverdueInvoiceScanner,
 * CustomerSuspender, dan WhatsappNotifier bisa berjalan di atas skema tersebut.
 *
 * Nomor migration diletakkan SETELAH create_automation_tables (080000) karena
 * foreign key suspended_by_id mengacu ke tabel users dan index-nya menumpang
 * tabel pelanggans yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->string('customer_number', 50)->nullable()->after('tenant_id');
            $table->string('whatsapp_number', 30)->nullable()->after('telepon');
            $table->foreignUlid('user_id')->nullable()->after('tenant_id');
            $table->string('suspension_source', 20)->nullable()->after('status');
            $table->text('status_reason')->nullable()->after('suspension_source');
            $table->timestamp('suspended_at')->nullable()->after('status_reason');
            $table->foreignUlid('suspended_by_id')->nullable()->after('suspended_at');
            $table->date('joined_at')->nullable()->after('tanggal_aktif');
            $table->timestamp('archived_at')->nullable()->after('joined_at');

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('suspended_by_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['tenant_id', 'customer_number'], 'pelanggans_tenant_customer_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropUnique('pelanggans_tenant_customer_number_unique');
            $table->dropForeign(['suspended_by_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'customer_number',
                'whatsapp_number',
                'user_id',
                'suspension_source',
                'status_reason',
                'suspended_at',
                'suspended_by_id',
                'joined_at',
                'archived_at',
            ]);
        });
    }
};
