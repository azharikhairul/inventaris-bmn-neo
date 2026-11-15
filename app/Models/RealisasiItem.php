<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiItem extends Model
{
    use HasFactory;

    protected $table = 'realisasi_items';

    protected $guarded = ['id'];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    public function realisasiAset()
    {
        return $this->belongsTo(RealisasiAset::class);
    }
}
