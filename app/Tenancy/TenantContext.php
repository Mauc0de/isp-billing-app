<?php

namespace App\Tenancy;

use Closure;
use LogicException;

class TenantContext
{
    private ?string $tenantId = null;

    public function id(): ?string
    {
        return $this->tenantId;
    }

    public function set(string $tenantId): void
    {
        if ($tenantId === '') {
            throw new LogicException('Tenant ID cannot be empty.');
        }

        $this->tenantId = $tenantId;
    }

    public function forget(): void
    {
        $this->tenantId = null;
    }

    public function run(string $tenantId, Closure $callback): mixed
    {
        $previousTenantId = $this->tenantId;

        $this->set($tenantId);

        try {
            return $callback();
        } finally {
            $this->tenantId = $previousTenantId;
        }
    }
}
