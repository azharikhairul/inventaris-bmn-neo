<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'volume' => 'integer',
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    /**
     * The permintaan-aset entry this RAB item belongs to.
     */
    public function permintaanAset()
    {
        return $this->belongsTo(PermintaanAset::class);
    }
}
