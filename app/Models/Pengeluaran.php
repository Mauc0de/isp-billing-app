<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use BelongsToTenant;
    use HasFactory, HasUlids;

    protected $table = 'pengeluarans';

    protected $fillable = [
        'tenant_id',
        'tanggal',
        'jumlah',
        'kategori',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
    ];
}
