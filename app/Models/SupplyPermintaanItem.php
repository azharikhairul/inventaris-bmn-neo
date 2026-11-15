<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyPermintaanItem extends Model
{
    use HasFactory;

    protected $table = 'supply_permintaan_items';

    protected $guarded = ['id'];

    protected $casts = [
        'jumlah_diminta' => 'integer',
        'jumlah_disetujui' => 'integer',
    ];

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(SupplyPermintaan::class, 'supply_permintaan_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }
}
