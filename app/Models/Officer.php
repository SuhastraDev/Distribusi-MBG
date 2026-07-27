<?php

namespace App\Models;

use Database\Factories\OfficerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'officer_code', 'name', 'phone', 'address', 'status'])]
class Officer extends Model
{
    /** @use HasFactory<OfficerFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Officer>  $query
     * @return Builder<Officer>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('status', 'active'));
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->user?->status === 'active';
    }

    /**
     * @return HasMany<DistributionSchedule, $this>
     */
    public function distributionSchedules(): HasMany
    {
        return $this->hasMany(DistributionSchedule::class);
    }

    /**
     * @return HasMany<DistributionRun, $this>
     */
    public function distributionRuns(): HasMany
    {
        return $this->hasMany(DistributionRun::class);
    }

    /**
     * @return HasMany<OfficerPosition, $this>
     */
    public function positions(): HasMany
    {
        return $this->hasMany(OfficerPosition::class);
    }
}
