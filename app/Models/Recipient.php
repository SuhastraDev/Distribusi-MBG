<?php

namespace App\Models;

use Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['location_id', 'code', 'name', 'portion_count', 'notes', 'status'])]
class Recipient extends Model
{
    /** @use HasFactory<RecipientFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'portion_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @param  Builder<Recipient>  $query
     * @return Builder<Recipient>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereHas('location', fn (Builder $locationQuery): Builder => $locationQuery->where('status', 'active'));
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->location?->status === 'active';
    }

    /**
     * @return HasMany<DistributionScheduleDestination, $this>
     */
    public function scheduleDestinations(): HasMany
    {
        return $this->hasMany(DistributionScheduleDestination::class);
    }
}
