<?php

namespace App\Models;

use App\Enums\VoucherStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'paket_id',
        'kode',
        'harga',
        'masa_aktif_hari',
        'status',
        'pelanggan_id',
        'dipakai_at',
        'kadaluarsa_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'masa_aktif_hari' => 'integer',
            'status' => VoucherStatus::class,
            'dipakai_at' => 'datetime',
            'kadaluarsa_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(VoucherBatch::class, 'batch_id');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
