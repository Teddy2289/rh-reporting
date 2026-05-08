<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class HourLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id', 'date', 'worked_minutes',
        'expected_minutes', 'overtime_minutes',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function getWorkedHoursAttribute(): float
    {
        return round($this->worked_minutes / 60, 2);
    }

    public function getExpectedHoursAttribute(): float
    {
        return round($this->expected_minutes / 60, 2);
    }

    public function getOvertimeHoursAttribute(): float
    {
        return round($this->overtime_minutes / 60, 2);
    }

    public function scopeForAgent(Builder $query, int $agentId): Builder
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
