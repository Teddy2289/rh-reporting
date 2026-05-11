<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $table = 'leave_balances';

    protected $fillable = [
        'agent_id',
        'year',
        'allocated_days',      // Jours alloués pour l'année (acquis)
        'used_days',           // Jours utilisés (approuvés)
        'pending_days',        // Jours en attente
        'carried_over_days',   // Jours reportés de l'année précédente
        'last_updated_at'
    ];

    protected $casts = [
        'allocated_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
        'carried_over_days' => 'decimal:2',
        'last_updated_at' => 'datetime',
        'year' => 'integer'
    ];

    // Accesseurs
    public function getRemainingDaysAttribute(): float
    {
        $total = $this->allocated_days + $this->carried_over_days;
        $used = $this->used_days + $this->pending_days;

        return max(0, $total - $used);
    }

    public function getTotalAvailableAttribute(): float
    {
        return $this->allocated_days + $this->carried_over_days;
    }

    // Relations
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    // Methods
    public function deductPendingDays(float $days): void
    {
        $this->pending_days += $days;
        $this->save();
    }

    public function approvePendingDays(float $days): void
    {
        $this->pending_days -= $days;
        $this->used_days += $days;
        $this->save();
    }

    public function rejectPendingDays(float $days): void
    {
        $this->pending_days -= $days;
        $this->save();
    }

    public function addAllocatedDays(float $days): void
    {
        $this->allocated_days += $days;
        $this->save();
    }

    public function canTakeLeave(float $days): bool
    {
        return $this->remaining_days >= $days;
    }
}
