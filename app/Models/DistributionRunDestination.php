<?php

namespace App\Models;

use Database\Factories\DistributionRunDestinationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'distribution_run_id',
    'distribution_schedule_destination_id',
    'location_id',
    'recipient_id',
    'planned_portion_count',
    'delivered_portion_count',
    'sequence_order',
    'status',
    'arrived_at',
    'delivered_at',
    'proof_notes',
])]
class DistributionRunDestination extends Model
{
    /** @use HasFactory<DistributionRunDestinationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_portion_count' => 'integer',
            'delivered_portion_count' => 'integer',
            'sequence_order' => 'integer',
            'arrived_at' => 'datetime',
            'delivered_at' => 'datetime',
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
     * @return BelongsTo<DistributionScheduleDestination, $this>
     */
    public function scheduleDestination(): BelongsTo
    {
        return $this->belongsTo(DistributionScheduleDestination::class, 'distribution_schedule_destination_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<Recipient, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }
}
