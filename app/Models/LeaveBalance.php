<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id', 'year', 'allocated_days',
        'used_days', 'pending_days', 'carried_over_days',
    ];

    protected function casts(): array
    {
        return [
            'allocated_days'    => 'decimal:1',
            'used_days'         => 'decimal:1',
            'pending_days'      => 'decimal:1',
            'carried_over_days' => 'decimal:1',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function getRemainingDaysAttribute(): float
    {
        return $this->allocated_days + $this->carried_over_days
             - $this->used_days - $this->pending_days;
    }
}
