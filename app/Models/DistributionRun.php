<?php

namespace App\Models;

use Database\Factories\DistributionRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['code', 'distribution_schedule_id', 'officer_id', 'status', 'started_at', 'completed_at', 'notes'])]
class DistributionRun extends Model
{
    /** @use HasFactory<DistributionRunFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<DistributionSchedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DistributionSchedule::class, 'distribution_schedule_id');
    }

    /**
     * @return BelongsTo<Officer, $this>
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }

    /**
     * @return HasMany<DistributionRunDestination, $this>
     */
    public function destinations(): HasMany
    {
        return $this->hasMany(DistributionRunDestination::class);
    }

    /**
     * @return HasOne<RoutePlan, $this>
     */
    public function routePlan(): HasOne
    {
        return $this->hasOne(RoutePlan::class);
    }

    public function canBeStarted(): bool
    {
        return $this->status === 'ready';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress'
            && $this->destinations()->whereIn('status', ['pending', 'arrived'])->doesntExist();
    }

    public function deliveredPortions(): int
    {
        return (int) $this->destinations()->where('status', 'delivered')->sum('delivered_portion_count');
    }
}
