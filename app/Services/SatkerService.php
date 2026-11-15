<?php

namespace App\Services;

use App\Models\Satker;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SatkerService
{
    /**
     * Check if multisatker feature is enabled.
     */
    public static function isMultiSatkerEnabled(): bool
    {
        return (bool) config('satker.enabled', true);
    }

    /**
     * Get the default/induk satker ID (BPS Provinsi Gorontalo).
     */
    public static function getIndukSatkerId(): int
    {
        $provinsi = Satker::where('tingkat', 'Provinsi')->first();
        if ($provinsi) {
            return (int) $provinsi->id;
        }

        $def = Satker::where('kode_satker', config('satker.default_kode_satker'))->first();

        return (int) ($def?->id ?? 1);
    }

    /**
     * Check if the current authenticated user is viewing data of another satker (Read-Only Mode).
     * Returns true if user can access all satkers AND has switched the active satker to another satker.
     */
    public static function isViewingOtherSatker(): bool
    {
        if (! self::isMultiSatkerEnabled()) {
            return false;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->canAccessAllSatkers()) {
            return false;
        }

        $activeSatkerId = self::getActiveSatkerId();
        if (! $activeSatkerId) {
            return false;
        }

        $homeSatkerId = (int) ($user->satker_id ?? self::getIndukSatkerId());

        return (int) $activeSatkerId !== $homeSatkerId;
    }

    /**
     * Check if user can add, edit, or delete data in the current satker scope.
     */
    public static function canManageCurrentScope(): bool
    {
        return ! self::isViewingOtherSatker();
    }

    /**
     * Abort the request if the current user is in read-only mode viewing another satker.
     */
    public static function abortIfViewingOtherSatker(?string $action = null): void
    {
        if (self::isViewingOtherSatker()) {
            $satkerName = self::getActiveSatker()?->nama_satker ?? 'satker lain';
            $actionLabel = $action ?? 'penambahan atau perubahan data';
            $msg = "Akses Dibatasi (Mode Lihat Saja): Anda sedang melihat data {$satkerName}. Tindakan {$actionLabel} hanya dapat dilakukan oleh Admin satker terkait atau saat berada di lingkup Satker Induk.";

            if (request()->wantsJson() || request()->ajax()) {
                abort(response()->json([
                    'success' => false,
                    'message' => $msg,
                    'error' => $msg,
                ], 403));
            }

            abort(403, $msg);
        }
    }

    /**
     * Get active satker ID for current context.
     * Returns null if 'Semua Satker' is selected or multisatker disabled.
     */
    public static function getActiveSatkerId(): ?int
    {
        if (! self::isMultiSatkerEnabled()) {
            return null;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->canAccessAllSatkers()) {
            $sess = Session::get('active_satker_id');
            if ($sess === 'all') {
                return null;
            }

            if ($sess !== null) {
                return (int) $sess;
            }

            return $user->satker_id ?? self::getIndukSatkerId();
        }

        return $user->satker_id ?? self::getIndukSatkerId();
    }

    /**
     * Get active Satker model.
     */
    public static function getActiveSatker(): ?Satker
    {
        $id = self::getActiveSatkerId();
        if (! $id) {
            return null;
        }

        return Satker::find($id);
    }

    /**
     * Set active satker filter for Super Admin / Admin Provinsi.
     */
    public static function setActiveSatker(?int $satkerId): void
    {
        if ($satkerId === null || $satkerId === 0) {
            Session::put('active_satker_id', 'all');
        } else {
            Session::put('active_satker_id', $satkerId);
        }
    }

    /**
     * Get list of all active satkers.
     */
    public static function getAllSatkers()
    {
        return Satker::where('is_active', true)
            ->orderByRaw("CASE WHEN tingkat = 'Provinsi' THEN 0 ELSE 1 END")
            ->orderBy('nama_satker')
            ->get();
    }

    /**
     * Apply satker filtering on Eloquent Query Builder.
     */
    public static function filterQuery($query, string $column = 'satker_id')
    {
        if (! self::isMultiSatkerEnabled()) {
            return $query;
        }

        $activeSatkerId = self::getActiveSatkerId();
        if (! $activeSatkerId) {
            return $query;
        }

        if ($column === 'kode_satker') {
            $satker = self::getActiveSatker();
            if ($satker) {
                return $query->where('kode_satker', $satker->kode_satker);
            }

            return $query;
        }

        return $query->where($column, $activeSatkerId);
    }
}
