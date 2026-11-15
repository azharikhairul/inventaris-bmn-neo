<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyMutasi extends Model
{
    use HasFactory;

    protected $table = 'supply_mutasis';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
        'stok_sebelum' => 'integer',
        'stok_sesudah' => 'integer',
    ];

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function distribusi(): BelongsTo
    {
        return $this->belongsTo(SupplyDistribusi::class, 'supply_distribusi_id');
    }

    public function realisasi(): BelongsTo
    {
        return $this->belongsTo(Realisasi::class, 'realisasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
