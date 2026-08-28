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

    /**
     * Two free OSRM demo hosts, each wrong in a different way on their own:
     *
     * - "car" (router.project-osrm.org) refuses many small gang/lorong
     *   (motor_vehicle=no), forcing multi-km detours via major roads even
     *   where a short local street connects two points.
     * - "bike" (routing.openstreetmap.de) isn't blocked by those car-only
     *   restrictions, but its cost function actively avoids busy arterial
     *   roads - for two points that are genuinely best reached via a main
     *   road, it has been observed picking a route 8x longer than the car
     *   profile's (72km vs 8.6km for the same two points).
     *
     * Neither is right alone. Both matrices are fetched and combined by
     * taking the shorter distance per pair, so a big road is used when it's
     * genuinely the shortest option and a gang/lorong is used when that's
     * shorter instead, instead of either profile's blind spot dominating.
     *
     * @var array<string, string>
     */
    private const OSRM_HOSTS = [
        'car' => 'https://router.project-osrm.org',
        'bike' => 'https://routing.openstreetmap.de/routed-bike',
    ];

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

            // One matrix call per host for real road distances between the depot
            // and every destination. The greedy loop below picks each next stop
            // using the shorter of the two (falls back to straight-line Haversine,
            // consistently for both ordering and reporting, if neither matrix is
            // available) so the "nearest" stop it picks is actually nearest to
            // drive to, and the reported distance/time matches the same criterion
            // used to pick it.
            $roadMatrix = $this->fetchCombinedRoadDistanceMatrix($depot, $destinations);

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
     * by [fromLocationId][toLocationId], taking the shorter of the car-profile
     * and bike-profile distance for each pair. Returns null only if both hosts
     * are unreachable, so callers fall back to Haversine for both ordering and
     * distance reporting.
     *
     * @param  Collection<int, DistributionRunDestination>  $destinations
     * @return array<int, array<int, float>>|null
     */
    private function fetchCombinedRoadDistanceMatrix(Location $depot, Collection $destinations): ?array
    {
        $carMatrix = $this->fetchRoadDistanceMatrix($depot, $destinations, self::OSRM_HOSTS['car']);
        $bikeMatrix = $this->fetchRoadDistanceMatrix($depot, $destinations, self::OSRM_HOSTS['bike']);

        if ($carMatrix === null && $bikeMatrix === null) {
            return null;
        }

        if ($carMatrix === null) {
            return $bikeMatrix;
        }

        if ($bikeMatrix === null) {
            return $carMatrix;
        }

        $combined = [];
        foreach ($carMatrix as $fromId => $row) {
            foreach ($row as $toId => $carDistance) {
                $bikeDistance = $bikeMatrix[$fromId][$toId] ?? null;
                $combined[$fromId][$toId] = $bikeDistance === null
                    ? $carDistance
                    : min($carDistance, $bikeDistance);
            }
        }

        return $combined;
    }

    /**
     * @param  Collection<int, DistributionRunDestination>  $destinations
     * @return array<int, array<int, float>>|null
     */
    private function fetchRoadDistanceMatrix(Location $depot, Collection $destinations, string $osrmHost): ?array
    {
        $locations = collect([$depot])->concat($destinations->map->location)->unique('id')->values();
        $coordsStr = $locations->map(fn (Location $loc) => $loc->longitude.','.$loc->latitude)->join(';');

        try {
            $response = Http::timeout(10)->get($osrmHost.'/table/v1/driving/'.$coordsStr, [
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
     * Compares full path totals rather than just the two boundary edges around
     * a candidate segment. Real road distances from one-way streets are
     * directional (A->B often isn't B->A), so reversing a segment changes the
     * cost of every edge inside it, not only the two at its ends - a
     * boundary-only comparison can accept a swap that actually makes the route
     * longer once those flipped internal edges are accounted for.
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

        $pathCost = function (array $path) use ($depot, $roadMatrix): float {
            $total = 0.0;
            $previous = $depot;
            foreach ($path as $destination) {
                $total += $this->distanceBetween($previous, $destination->location, $roadMatrix);
                $previous = $destination->location;
            }

            return $total;
        };

        $improved = true;
        $iterations = 0;
        $maxIterations = 100;

        while ($improved && $iterations < $maxIterations) {
            $improved = false;
            $iterations++;
            $currentTotal = $pathCost($ordered);

            for ($i = 0; $i < $count - 1; $i++) {
                for ($j = $i + 1; $j < $count; $j++) {
                    $candidate = $ordered;
                    $segment = array_reverse(array_slice($candidate, $i, $j - $i + 1));
                    array_splice($candidate, $i, $j - $i + 1, $segment);

                    $candidateTotal = $pathCost($candidate);

                    if ($candidateTotal < $currentTotal - 1e-9) {
                        $ordered = $candidate;
                        $currentTotal = $candidateTotal;
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
