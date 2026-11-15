<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanAset extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The permintaan that this entry belongs to.
     */
    public function permintaan()
    {
        return $this->belongsTo(Permintaan::class);
    }

    /**
     * The aset linked to this permintaan entry.
     */
    public function aset()
    {
        return $this->belongsTo(Aset::class);
    }

    /**
     * RAB line items for this permintaan-aset pair.
     */
    public function rabItems()
    {
        return $this->hasMany(RabItem::class);
    }
}
