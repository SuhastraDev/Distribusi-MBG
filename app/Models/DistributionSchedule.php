<?php

namespace App\Models;

use Database\Factories\DistributionScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'scheduled_date', 'officer_id', 'depot_location_id', 'total_portions', 'status', 'notes'])]
class DistributionSchedule extends Model
{
    /** @use HasFactory<DistributionScheduleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'total_portions' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Officer, $this>
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'depot_location_id');
    }

    /**
     * @return HasMany<DistributionScheduleDestination, $this>
     */
    public function destinations(): HasMany
    {
        return $this->hasMany(DistributionScheduleDestination::class);
    }

    public function recalculateTotalPortions(): void
    {
        $this->update([
            'total_portions' => $this->destinations()->sum('portion_count'),
        ]);
    }
}
