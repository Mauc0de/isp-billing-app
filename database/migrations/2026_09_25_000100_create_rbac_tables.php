<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 100);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'roles_id_tenant_unique');
            $table->unique(['tenant_id', 'slug'], 'roles_tenant_slug_unique');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('permission_group', 50);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('permission_group');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignUlid('tenant_id');
            $table->foreignUlid('role_id');
            $table->foreignUlid('user_id');

            $table->primary(['user_id', 'role_id'], 'role_user_primary');
            $table->foreign(['role_id', 'tenant_id'], 'role_user_role_tenant_foreign')
                ->references(['id', 'tenant_id'])
                ->on('roles')
                ->cascadeOnDelete();
            $table->foreign(['user_id', 'tenant_id'], 'role_user_user_tenant_foreign')
                ->references(['id', 'tenant_id'])
                ->on('users')
                ->cascadeOnDelete();
            $table->index(['tenant_id', 'role_id'], 'role_user_tenant_role_idx');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignUlid('tenant_id');
            $table->foreignUlid('role_id');
            $table->foreignUlid('permission_id');

            $table->primary(['role_id', 'permission_id'], 'permission_role_primary');
            $table->foreign(['role_id', 'tenant_id'], 'permission_role_role_tenant_foreign')
                ->references(['id', 'tenant_id'])
                ->on('roles')
                ->cascadeOnDelete();
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();
            $table->index(['tenant_id', 'permission_id'], 'permission_role_tenant_perm_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
