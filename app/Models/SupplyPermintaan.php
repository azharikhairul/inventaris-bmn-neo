<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupplyPermintaan extends Model
{
    use HasFactory;

    protected $table = 'supply_permintaans';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_permintaan' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SupplyPermintaanItem::class);
    }

    public function distribusi(): HasOne
    {
        return $this->hasOne(SupplyDistribusi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to generate human-friendly request number.
     */
    public static function generateNomorPermintaan(): string
    {
        $prefix = 'REQ-SUP/'.date('Ym').'/';
        $last = static::where('nomor_permintaan', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last->nomor_permintaan);
            $seq = ((int) end($parts)) + 1;
        }

        return $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
