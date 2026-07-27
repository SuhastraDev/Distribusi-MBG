<?php

namespace App\Models;

use Database\Factories\OfficerPositionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distribution_run_id', 'officer_id', 'latitude', 'longitude', 'accuracy_meters', 'recorded_at'])]
class OfficerPosition extends Model
{
    /** @use HasFactory<OfficerPositionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_meters' => 'decimal:2',
            'recorded_at' => 'datetime',
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
     * @return BelongsTo<Officer, $this>
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(Officer::class);
    }
}
