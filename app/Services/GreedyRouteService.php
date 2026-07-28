<?php

namespace App\Services;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\Location;
use App\Models\RoutePlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GreedyRouteService
{
    private const AVERAGE_SPEED_KM_PER_HOUR = 25;

    public function generate(DistributionRun $distributionRun): RoutePlan
    {
        $distributionRun->load([
            'schedule.depot',
            'destinations.location',
        ]);

        $depot = $distributionRun->schedule->depot;
        $destinations = $distributionRun->destinations;

        $this->validateCoordinates($depot, $destinations);

        return DB::transaction(function () use ($distributionRun, $depot, $destinations): RoutePlan {
            $routePlan = RoutePlan::query()->updateOrCreate(
                ['distribution_run_id' => $distributionRun->id],
                [
                    'code' => $distributionRun->routePlan?->code ?? $this->generateCode(),
                    'algorithm' => 'greedy_nearest_neighbor',
                    'status' => 'generated',
                    'notes' => 'Rute dibuat otomatis dengan algoritma Greedy nearest neighbor.',
                ]
            );

            $routePlan->steps()->delete();

            $orderedDestinations = $this->orderDestinations($depot, $destinations);
            
            // Get actual road distances from OSRM API
            $locations = collect([$depot])->concat(collect($orderedDestinations)->map->location);
            $coordsStr = $locations->map(fn($loc) => $loc->longitude . ',' . $loc->latitude)->join(';');
            
            $osrmDistances = [];
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->get("https://router.project-osrm.org/route/v1/driving/{$coordsStr}?overview=false");
                if ($response->successful() && isset($response['routes'][0]['legs'])) {
                    foreach ($response['routes'][0]['legs'] as $leg) {
                        $osrmDistances[] = $leg['distance'] / 1000.0; // convert meters to km
                    }
                }
            } catch (\Exception $e) {
                // Fallback to Haversine if API fails
            }

            $currentLocation = $depot;
            $cumulativeDistance = 0.0;

            $routePlan->steps()->create([
                'location_id' => $depot->id,
                'step_order' => 1,
                'step_type' => 'start',
                'distance_from_previous_km' => 0,
                'cumulative_distance_km' => 0,
            ]);

            foreach ($orderedDestinations as $index => $destination) {
                $distance = isset($osrmDistances[$index]) 
                    ? $osrmDistances[$index] 
                    : $this->distanceInKm($currentLocation, $destination->location);
                    
                $cumulativeDistance += $distance;

                $routePlan->steps()->create([
                    'distribution_run_destination_id' => $destination->id,
                    'location_id' => $destination->location_id,
                    'step_order' => $index + 2,
                    'step_type' => 'destination',
                    'distance_from_previous_km' => round($distance, 3),
                    'cumulative_distance_km' => round($cumulativeDistance, 3),
                ]);

                $currentLocation = $destination->location;
            }

            $routePlan->update([
                'total_distance_km' => round($cumulativeDistance, 3),
                'total_estimated_minutes' => $this->estimateMinutes($cumulativeDistance),
            ]);

            return $routePlan->fresh(['run.schedule.depot', 'steps.location', 'steps.runDestination.recipient']);
        });
    }

    /**
     * @param  Collection<int, DistributionRunDestination>  $destinations
     * @return array<int, DistributionRunDestination>
     */
    public function orderDestinations(Location $depot, Collection $destinations): array
    {
        $remaining = $destinations->values();
        $ordered = [];
        $currentLocation = $depot;

        while ($remaining->isNotEmpty()) {
            $nearest = $remaining
                ->sortBy(fn (DistributionRunDestination $destination): float => $this->distanceInKm($currentLocation, $destination->location))
                ->first();

            $ordered[] = $nearest;
            $currentLocation = $nearest->location;
            $remaining = $remaining->reject(fn (DistributionRunDestination $destination): bool => $destination->id === $nearest->id)->values();
        }

        return $ordered;
    }

    public function distanceInKm(Location $from, Location $to): float
    {
        $earthRadiusKm = 6371;
        $latFrom = deg2rad((float) $from->latitude);
        $lonFrom = deg2rad((float) $from->longitude);
        $latTo = deg2rad((float) $to->latitude);
        $lonTo = deg2rad((float) $to->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2
        ));

        return $earthRadiusKm * $angle;
    }

    private function estimateMinutes(float $distanceKm): int
    {
        if ($distanceKm <= 0) {
            return 0;
        }

        return (int) ceil(($distanceKm / self::AVERAGE_SPEED_KM_PER_HOUR) * 60);
    }

    private function generateCode(): string
    {
        do {
            $code = 'RTE-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (RoutePlan::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * @param  Collection<int, DistributionRunDestination>  $destinations
     */
    private function validateCoordinates(Location $depot, Collection $destinations): void
    {
        if ($destinations->isEmpty()) {
            throw ValidationException::withMessages([
                'distribution_run_id' => 'Distribusi harus memiliki minimal satu tujuan untuk dibuatkan rute.',
            ]);
        }

        if ($depot->latitude === null || $depot->longitude === null) {
            throw ValidationException::withMessages([
                'distribution_run_id' => 'Koordinat depot belum lengkap.',
            ]);
        }

        $missingDestination = $destinations->first(
            fn (DistributionRunDestination $destination): bool => $destination->location->latitude === null
                || $destination->location->longitude === null
        );

        if ($missingDestination !== null) {
            throw ValidationException::withMessages([
                'distribution_run_id' => 'Semua tujuan distribusi harus memiliki latitude dan longitude.',
            ]);
        }
    }
}
