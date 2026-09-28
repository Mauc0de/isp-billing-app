<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_paket');
            $table->string('kecepatan', 50)->nullable();
            $table->integer('harga')->default(0);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'nama_paket', 'kecepatan', 'harga', 'deskripsi', 'status']);
        });
    }
};
