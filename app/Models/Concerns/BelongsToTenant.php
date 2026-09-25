<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = app(TenantContext::class)->id();

            if ($tenantId === null) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where(
                $builder->getModel()->qualifyColumn('tenant_id'),
                $tenantId,
            );
        });

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);
            $contextTenantId = $context->id();
            $modelTenantId = $model->getAttribute('tenant_id');

            if ($modelTenantId === null && $contextTenantId !== null) {
                $model->setAttribute('tenant_id', $contextTenantId);
                $modelTenantId = $contextTenantId;
            }

            if ($modelTenantId === null) {
                throw new LogicException('A tenant context is required to create a tenant-owned model.');
            }

            if ($contextTenantId !== null && $modelTenantId !== $contextTenantId) {
                throw new LogicException('The model tenant does not match the active tenant context.');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
