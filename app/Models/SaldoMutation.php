<?php

namespace App\Models;

use App\Enums\SaldoMutationType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoMutation extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'pelanggan_id',
        'jenis',
        'jumlah',
        'saldo_akhir',
        'sumber',
        'referensi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => SaldoMutationType::class,
            'jumlah' => 'integer',
            'saldo_akhir' => 'integer',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
