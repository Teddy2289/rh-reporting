<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clients extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'color',
        'contact_email',
        'contact_phone',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

  public function missions(): HasMany
{
    return $this->hasMany(Mission::class, 'client_id');
}

    public function planningSlots(): HasMany
    {
        return $this->hasMany(PlanningSlot::class);
    }
}
