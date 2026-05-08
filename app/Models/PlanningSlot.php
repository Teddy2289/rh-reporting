<?php

namespace App\Models;

use App\Enums\SlotType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PlanningSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'client_id',
        'mission_id',
        'date',
        'time_start',
        'time_end',
        'type',
        'note',
        'is_confirmed',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'type'         => SlotType::class,
            'date'         => 'date',
            'is_confirmed' => 'boolean',
        ];
    }

    // ─── Accessors ──────────────────────────────────────────────────────────

    public function getDurationMinutesAttribute(): int
    {
        $start = Carbon::parse($this->time_start);
        $end   = Carbon::parse($this->time_end);
        return (int) $start->diffInMinutes($end);
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 2);
    }

    // ─── Relations ──────────────────────────────────────────────────────────

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForAgent(Builder $query, int $agentId): Builder
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeWorked(Builder $query): Builder
    {
        return $query->where('type', SlotType::Work->value);
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    // ─── Static helpers ─────────────────────────────────────────────────────

    public static function workedMinutesForDate(int $agentId, string $date): int
    {
        return self::forAgent($agentId)
            ->forDate($date)
            ->worked()
            ->get()
            ->sum('duration_minutes');
    }
}
