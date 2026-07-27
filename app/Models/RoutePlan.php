<?php

namespace App\Models;

use Database\Factories\RoutePlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'distribution_run_id', 'algorithm', 'total_distance_km', 'total_estimated_minutes', 'status', 'notes'])]
class RoutePlan extends Model
{
    /** @use HasFactory<RoutePlanFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_distance_km' => 'decimal:3',
            'total_estimated_minutes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<DistributionRun, $this>
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(DistributionRun::class, 'distribution_run_id');
    }

    /**
     * @return HasMany<RoutePlanStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(RoutePlanStep::class)->orderBy('step_order');
    }
}
