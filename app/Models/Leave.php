<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Leave extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'type',
        'status',
        'date_start',
        'date_end',
        'working_days',
        'reason',
        'refusal_reason',
        'approved_by',
        'approved_at',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'type'        => LeaveType::class,
            'status'      => LeaveStatus::class,
            'date_start'  => 'date',
            'date_end'    => 'date',
            'approved_at' => 'datetime',
            'working_days' => 'decimal:1'
        ];
    }

    public function getWorkingDaysLabelAttribute(): string
    {
        $days = (float) $this->working_days;

        if ($days === 0.5) {
            return '½ journée';
        }

        return $days . ' ' . ($days <= 1 ? 'jour' : 'jours');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Pending->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Approved->value);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('date_start', $year);
    }

    public function isPending(): bool
    {
        return $this->status === LeaveStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === LeaveStatus::Approved;
    }
}
