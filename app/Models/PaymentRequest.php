<?php

namespace App\Models;

use App\Enums\PaymentRequestPurpose;
use App\Enums\PaymentRequestStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequest extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'pelanggan_id',
        'tagihan_id',
        'tujuan',
        'jumlah',
        'metode',
        'status',
        'provider',
        'provider_reference',
        'checkout_url',
        'bukti_path',
        'catatan_pelanggan',
        'catatan_admin',
        'verified_by_id',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'tujuan' => PaymentRequestPurpose::class,
            'status' => PaymentRequestStatus::class,
            'jumlah' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }
}
