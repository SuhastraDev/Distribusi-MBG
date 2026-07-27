<?php

namespace App\Models;

use Database\Factories\RoutePlanStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'route_plan_id',
    'distribution_run_destination_id',
    'location_id',
    'step_order',
    'step_type',
    'distance_from_previous_km',
    'cumulative_distance_km',
])]
class RoutePlanStep extends Model
{
    /** @use HasFactory<RoutePlanStepFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'distance_from_previous_km' => 'decimal:3',
            'cumulative_distance_km' => 'decimal:3',
        ];
    }

    /**
     * @return BelongsTo<RoutePlan, $this>
     */
    public function routePlan(): BelongsTo
    {
        return $this->belongsTo(RoutePlan::class);
    }

    /**
     * @return BelongsTo<DistributionRunDestination, $this>
     */
    public function runDestination(): BelongsTo
    {
        return $this->belongsTo(DistributionRunDestination::class, 'distribution_run_destination_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
