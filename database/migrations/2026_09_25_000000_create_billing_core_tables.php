<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('billing_cycle', 32)->default('monthly');
            $table->unsignedSmallInteger('billing_period_days')->nullable();
            $table->unsignedInteger('price_down_mbps')->nullable();
            $table->unsignedInteger('price_up_mbps')->nullable();
            $table->decimal('price', 15, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'name'], 'packages_tenant_name_unique');
            $table->index(['tenant_id', 'is_active'], 'packages_tenant_active_idx');
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('suspended_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_number', 50);
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('suspension_source', 32)->nullable();
            $table->text('status_reason')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->date('joined_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'customer_number'], 'customers_tenant_number_unique');
            $table->index(['tenant_id', 'status'], 'customers_tenant_status_idx');
            $table->index(['tenant_id', 'phone'], 'customers_tenant_phone_idx');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('generated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_number', 50);
            $table->string('status', 32)->default('unpaid');
            $table->string('package_name');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamp('generated_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'invoice_number'], 'invoices_tenant_number_unique');
            $table->index(['tenant_id', 'status', 'due_date'], 'invoices_tenant_status_due_idx');
            $table->index(['tenant_id', 'customer_id'], 'invoices_tenant_customer_idx');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('method', 50);
            $table->string('status', 32)->default('paid');
            $table->string('reference')->nullable();
            $table->string('provider_reference')->nullable();
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'provider_reference'], 'payments_tenant_provider_ref_unique');
            $table->index(['tenant_id', 'paid_at'], 'payments_tenant_paid_at_idx');
            $table->index(['tenant_id', 'customer_id'], 'payments_tenant_customer_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('packages');
    }
};
