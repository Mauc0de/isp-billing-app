<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama');
            $table->string('telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->foreignId('paket_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['aktif', 'nonaktif', 'menunggu'])->default('aktif');
            $table->date('tanggal_aktif')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'nama', 'telepon', 'email', 'alamat', 'paket_id', 'status', 'tanggal_aktif']);
        });
    }
};
