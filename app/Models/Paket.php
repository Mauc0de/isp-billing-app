<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    use HasFactory;

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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
