<?php

namespace App\Models;

use App\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'manager_id',
        'employee_code',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'contract_type',
        'hire_date',
        'contract_end_date',
        'weekly_hours',
        'annual_leave_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'contract_type'     => ContractType::class,
            'hire_date'         => 'date',
            'contract_end_date' => 'date',
            'is_active'         => 'boolean',
        ];
    }

    // ─── Accessors ──────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset("storage/{$this->avatar}") : null;
    }

    public function getDailyExpectedMinutesAttribute(): int
    {
        return ($this->weekly_hours / 5) * 60; // minutes par jour (5j/semaine)
    }

    // ─── Relations ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Agent::class, 'manager_id');
    }

    public function planningSlots(): HasMany
    {
        return $this->hasMany(PlanningSlot::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function hourLogs(): HasMany
    {
        return $this->hasMany(HourLog::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByManager(Builder $query, int $managerId): Builder
    {
        return $query->where('manager_id', $managerId);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function leaveBalance(int $year): ?LeaveBalance
    {
        return $this->leaveBalances()->where('year', $year)->first();
    }
}
