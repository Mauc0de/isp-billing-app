<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat notifikasi Telegram ke admin.
 *
 * Berbeda dari wa_notifications yang punya antrean retry, notifikasi Telegram
 * di sini bersifat alert langsung ke admin; baris tetap dicatat agar kejadian
 * penting bisa diaudit walau gateway sedang nonaktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_notifications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 40);
            $table->string('chat_id')->nullable();
            $table->text('message');
            $table->string('status', 20)->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at'], 'telegram_notifications_tenant_created_idx');
            $table->index(['event', 'status'], 'telegram_notifications_event_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_notifications');
    }
};
