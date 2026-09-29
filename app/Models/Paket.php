<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $fillable = [
        'tenant_id',
        'nama_paket',
        'kecepatan',
        'harga',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'harga' => 'integer',
    ];

    public function pelanggan(): HasMany
    {
        return $this->hasMany(Pelanggan::class);
    }
}
