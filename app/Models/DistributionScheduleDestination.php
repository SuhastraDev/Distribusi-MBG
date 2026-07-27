<?php

namespace App\Models;

use Database\Factories\DistributionScheduleDestinationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distribution_schedule_id', 'location_id', 'recipient_id', 'portion_count', 'sequence_order'])]
class DistributionScheduleDestination extends Model
{
    /** @use HasFactory<DistributionScheduleDestinationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'portion_count' => 'integer',
            'sequence_order' => 'integer',
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
