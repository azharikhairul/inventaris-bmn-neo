<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\SatkerService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['satker_id', 'name', 'username', 'email', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Satker belonging to this user.
     */
    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }

    /**
     * Check if user has admin privileges.
     */
    public function isAdmin(): bool
    {
        return strtolower($this->role ?? '') === 'admin';
    }

    /**
     * Check if user is the primary protected administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->id === 2
            || strtolower($this->username ?? '') === 'admin'
            || strtolower($this->name ?? '') === 'administrator';
    }

    /**
     * Check if user can access data across all satkers.
     * Only Super Admin or Administrator at Provincial level can access/switch all satkers.
     */
    public function canAccessAllSatkers(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return strtolower($this->role ?? '') === 'admin'
            && ($this->satker === null || $this->satker->isProvinsi());
    }

    /**
     * Get or resolve the associated satker for this user.
     */
    public function resolveSatker(): ?Satker
    {
        if ($this->relationLoaded('satker') && $this->satker) {
            return $this->satker;
        }

        if ($this->satker_id) {
            $satker = Satker::find($this->satker_id);
            if ($satker) {
                return $satker;
            }
        }

        // Fallback to Induk Satker (BPS Provinsi Gorontalo)
        $indukId = SatkerService::getIndukSatkerId();
        $indukSatker = Satker::find($indukId);
        if ($indukSatker) {
            $this->satker_id = $indukSatker->id;
            $this->save();

            return $indukSatker;
        }

        return null;
    }

    /**
     * Find user by username or email.
     */
    public static function findByUsernameOrEmail(string $identifier): ?self
    {
        $clean = trim($identifier);
        $usernameOnly = str_contains($clean, '@') ? explode('@', $clean)[0] : $clean;

        return static::with('satker')
            ->where('username', $usernameOnly)
            ->orWhere('username', $clean)
            ->orWhere('username', strtolower($usernameOnly))
            ->orWhere('username', strtolower($clean))
            ->orWhere('email', $clean)
            ->orWhere('email', strtolower($clean))
            ->orWhere('email', $usernameOnly.'@bps.go.id')
            ->orWhere('email', strtolower($usernameOnly).'@bps.go.id')
            ->first();
    }
}
