<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fondasi Integrasi & Automasi (Backend 2).
 *
 * Menambahkan tabel routers, suspend_logs, wa_notifications, settings, serta
 * kolom pada pelanggans untuk menautkan pelanggan ke router tempat dia dilayani.
 *
 * Versi ini sudah disesuaikan dengan penamaan Indonesia yang dipakai repositori:
 * - customers  -> pelanggans  (kolom customer_id  -> pelanggan_id)
 * - invoices   -> tagihans    (kolom invoice_id   -> tagihan_id)
 * - packages   -> pakets      (kolom package_id   -> paket_id)
 *
 * Nomor migration sengaja diletakkan SETELAH 2026_09_25_072000_create_billing_tables
 * karena foreign key-nya mengacu ke tabel pelanggans & tagihans yang dibuat di sana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('vpn_ip', 45);
            $table->unsignedSmallInteger('api_port')->default(8728);
            $table->string('username');
            // Disimpan terenkripsi lewat cast 'encrypted' pada model.
            $table->text('password');
            $table->string('suspend_method', 32)->default('ppp_secret');
            $table->string('address_list_name')->nullable();
            $table->boolean('use_ssl')->default(false);
            $table->string('status', 32)->default('unknown');
            $table->timestamp('last_seen_at')->nullable();
            $table->text('last_error')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'name'], 'routers_tenant_name_unique');
            $table->unique(['tenant_id', 'vpn_ip'], 'routers_tenant_vpn_ip_unique');
            $table->index(['tenant_id', 'is_active'], 'routers_tenant_active_idx');
            $table->index(['tenant_id', 'status'], 'routers_tenant_status_idx');
        });

        // Pelanggan dilayani oleh satu router. Kolom nullable supaya aman untuk
        // data lama dan untuk tenant yang belum onboarding router sama sekali.
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->foreignUlid('router_id')->nullable()->after('paket_id');
            $table->string('mikrotik_username')->nullable()->after('router_id');

            $table->index(['tenant_id', 'router_id'], 'pelanggans_tenant_router_idx');
        });

        Schema::create('suspend_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignUlid('router_id')->nullable();
            $table->foreignUlid('tagihan_id')->nullable();
            $table->foreignUlid('performed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32);
            $table->string('source', 32);
            $table->string('method', 32)->nullable();
            $table->string('status', 32);
            $table->text('reason')->nullable();
            $table->text('error')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->nullOnDelete();
            $table->foreign('tagihan_id')->references('id')->on('tagihans')->nullOnDelete();

            $table->index(['tenant_id', 'pelanggan_id'], 'suspend_logs_tenant_pelanggan_idx');
            $table->index(['tenant_id', 'performed_at'], 'suspend_logs_tenant_performed_idx');
            $table->index(['tenant_id', 'action', 'status'], 'suspend_logs_tenant_action_idx');
        });

        Schema::create('wa_notifications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('pelanggan_id')->nullable()->constrained('pelanggans')->nullOnDelete();
            $table->foreignUlid('tagihan_id')->nullable()->constrained('tagihans')->nullOnDelete();
            $table->foreignUlid('suspend_log_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to_number', 30);
            $table->text('message');
            $table->string('provider', 32)->nullable();
            $table->string('status', 32)->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'wa_notifications_tenant_status_idx');
            $table->index(['tenant_id', 'created_at'], 'wa_notifications_tenant_created_idx');
            $table->index(['tenant_id', 'pelanggan_id'], 'wa_notifications_tenant_pelanggan_idx');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->json('value')->nullable();
            $table->string('type', 32)->default('string');
            $table->timestamps();

            $table->unique(['tenant_id', 'key'], 'settings_tenant_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('wa_notifications');
        Schema::dropIfExists('suspend_logs');

        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropIndex('pelanggans_tenant_router_idx');
            $table->dropColumn(['router_id', 'mikrotik_username']);
        });

        Schema::dropIfExists('routers');
    }
};
