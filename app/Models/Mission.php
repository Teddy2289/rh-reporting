<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['client_id', 'name', 'code', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

   public function client(): BelongsTo
{
    return $this->belongsTo(Clients::class, 'client_id');
}

    public function planningSlots(): HasMany
    {
        return $this->hasMany(PlanningSlot::class);
    }
}
