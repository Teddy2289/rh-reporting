<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'api';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Récupère l'identifiant qui sera stocké dans le sujet du JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Retourne un tableau contenant les revendications (claims) personnalisées à ajouter au JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->getRoleNames(), // Optionnel : ajoute les rôles au token
        ];
    }

    // ─── Relations ──────────────────────────────────────────────────────────

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isRh(): bool
    {
        return $this->hasRole('rh');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isAgent(): bool
    {
        return $this->hasRole('agent');
    }
}
