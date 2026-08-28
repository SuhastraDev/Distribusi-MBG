<?php

namespace App\Services;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\Location;
use App\Models\RoutePlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

            // One matrix call for real road distances between the depot and every
            // destination. The greedy loop below picks each next stop using these
            // real distances (falls back to straight-line Haversine, consistently
            // for both ordering and reporting, if the matrix isn't available) so
            // the "nearest" stop it picks is actually nearest to drive to, and the
            // reported distance/time matches the same criterion used to pick it.
            $roadMatrix = $this->fetchRoadDistanceMatrix($depot, $destinations);

            $orderedDestinations = $this->orderDestinations($depot, $destinations, $roadMatrix);

            // Nearest-neighbor greedy is short-sighted: it can lock in a stop that
            // looks best right now but forces a long backtrack later. A 2-opt pass
            // keeps the same greedy result as its starting point and only accepts
            // swaps that provably shorten the total distance, untangling that kind
            // of "muter-muter" detour without abandoning the greedy approach.
            $orderedDestinations = $this->twoOptImprove($depot, $orderedDestinations, $roadMatrix);

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
                $distance = $roadMatrix !== null
                    ? $roadMatrix[$currentLocation->id][$destination->location_id]
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
     * Real driving distances (km) between the depot and every destination, keyed
     * by [fromLocationId][toLocationId]. Returns null if OSRM couldn't be reached
     * or found no route, so callers fall back to Haversine for both ordering and
     * distance reporting.
     *
     * @param  Collection<int, DistributionRunDestination>  $destinations
     * @return array<int, array<int, float>>|null
     */
    private function fetchRoadDistanceMatrix(Location $depot, Collection $destinations): ?array
    {
        $locations = collect([$depot])->concat($destinations->map->location)->unique('id')->values();
        $coordsStr = $locations->map(fn (Location $loc) => $loc->longitude.','.$loc->latitude)->join(';');

        try {
            $response = Http::timeout(10)->get("https://router.project-osrm.org/table/v1/driving/{$coordsStr}", [
                'annotations' => 'distance',
            ]);

            if (! $response->successful() || ! isset($response['distances'])) {
                return null;
            }

            $distances = $response['distances'];
            if (count($distances) !== $locations->count()) {
                return null;
            }

            $matrix = [];
            foreach ($locations as $fromIndex => $fromLocation) {
                foreach ($locations as $toIndex => $toLocation) {
                    $meters = $distances[$fromIndex][$toIndex] ?? null;
                    if ($meters === null) {
                        return null;
                    }
                    $matrix[$fromLocation->id][$toLocation->id] = $meters / 1000.0;
                }
            }

            return $matrix;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param  Collection<int, DistributionRunDestination>  $destinations
     * @param  array<int, array<int, float>>|null  $roadMatrix
     * @return array<int, DistributionRunDestination>
     */
    public function orderDestinations(Location $depot, Collection $destinations, ?array $roadMatrix = null): array
    {
        $remaining = $destinations->values();
        $ordered = [];
        $currentLocation = $depot;

        while ($remaining->isNotEmpty()) {
            $nearest = $remaining
                ->sortBy(fn (DistributionRunDestination $destination): float => $this->distanceBetween($currentLocation, $destination->location, $roadMatrix))
                ->first();

            $ordered[] = $nearest;
            $currentLocation = $nearest->location;
            $remaining = $remaining->reject(fn (DistributionRunDestination $destination): bool => $destination->id === $nearest->id)->values();
        }

        return $ordered;
    }

    /**
     * Local-search refinement over a greedy-built path: repeatedly reverses a
     * segment of the route whenever doing so shortens the total distance,
     * starting from and re-using the same greedy order as its input. The depot
     * stays fixed as the start; the path is open (no return leg to the depot).
     *
     * @param  array<int, DistributionRunDestination>  $ordered
     * @param  array<int, array<int, float>>|null  $roadMatrix
     * @return array<int, DistributionRunDestination>
     */
    private function twoOptImprove(Location $depot, array $ordered, ?array $roadMatrix): array
    {
        $count = count($ordered);
        if ($count < 3) {
            return $ordered;
        }

        $locationAt = fn (int $index): Location => $index < 0 ? $depot : $ordered[$index]->location;

        $improved = true;
        $iterations = 0;
        $maxIterations = 100;

        while ($improved && $iterations < $maxIterations) {
            $improved = false;
            $iterations++;

            for ($i = 0; $i < $count - 1; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $before = $locationAt($i - 1);
                    $segmentStart = $locationAt($i);
                    $segmentEnd = $locationAt($j);
                    $after = $j + 1 < $count ? $locationAt($j + 1) : null;

                    $currentCost = $this->distanceBetween($before, $segmentStart, $roadMatrix)
                        + ($after !== null ? $this->distanceBetween($segmentEnd, $after, $roadMatrix) : 0.0);

                    $swappedCost = $this->distanceBetween($before, $segmentEnd, $roadMatrix)
                        + ($after !== null ? $this->distanceBetween($segmentStart, $after, $roadMatrix) : 0.0);

                    if ($swappedCost < $currentCost - 1e-9) {
                        $segment = array_reverse(array_slice($ordered, $i, $j - $i + 1));
                        array_splice($ordered, $i, $j - $i + 1, $segment);
                        $improved = true;
                    }
                }
            }
        }

        return $ordered;
    }

    private function distanceBetween(Location $from, Location $to, ?array $roadMatrix): float
    {
        if ($roadMatrix !== null) {
            return $roadMatrix[$from->id][$to->id];
        }

        return $this->distanceInKm($from, $to);
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
