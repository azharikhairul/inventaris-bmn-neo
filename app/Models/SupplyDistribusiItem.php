<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyDistribusiItem extends Model
{
    use HasFactory;

    protected $table = 'supply_distribusi_items';

    protected $guarded = ['id'];

    protected $casts = [
        'jumlah' => 'integer',
    ];

    public function distribusi(): BelongsTo
    {
        return $this->belongsTo(SupplyDistribusi::class, 'supply_distribusi_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }
}
