<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'name',
        'billing_cycle',
        'billing_period_days',
        'price_down_mbps',
        'price_up_mbps',
        'price',
        'description',
        'is_active',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
