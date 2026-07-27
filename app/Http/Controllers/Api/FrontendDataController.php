<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributionRun;
use App\Models\RoutePlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FrontendDataController extends Controller
{
    public function dashboardSummary(): JsonResponse
    {
        return response()->json([
            'distributions' => [
                'total' => DistributionRun::query()->count(),
                'ready' => DistributionRun::query()->where('status', 'ready')->count(),
                'in_progress' => DistributionRun::query()->where('status', 'in_progress')->count(),
                'completed' => DistributionRun::query()->where('status', 'completed')->count(),
                'cancelled' => DistributionRun::query()->where('status', 'cancelled')->count(),
            ],
            'routes' => [
                'total' => RoutePlan::query()->count(),
                'total_distance_km' => round((float) RoutePlan::query()->sum('total_distance_km'), 3),
            ],
            'latest_distributions' => DistributionRun::query()
                ->with(['schedule.depot', 'officer'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (DistributionRun $run): array => $this->runPayload($run)),
        ]);
    }

    public function distributionRuns(Request $request): JsonResponse
    {
        $runs = DistributionRun::query()
            ->with(['schedule.depot', 'officer', 'routePlan', 'latestOfficerPosition'])
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $runs->getCollection()->map(fn (DistributionRun $run): array => $this->runPayload($run)),
            'meta' => [
                'current_page' => $runs->currentPage(),
                'last_page' => $runs->lastPage(),
                'per_page' => $runs->perPage(),
                'total' => $runs->total(),
            ],
        ]);
    }

    public function distributionRunDetail(DistributionRun $distributionRun): JsonResponse
    {
        $distributionRun->load([
            'schedule.depot',
            'officer',
            'routePlan',
            'latestOfficerPosition',
            'destinations.location',
            'destinations.recipient',
        ]);

        return response()->json(array_merge(
            $this->runPayload($distributionRun),
            [
                'destinations' => $distributionRun->destinations
                    ->sortBy('sequence_order')
                    ->values()
                    ->map(fn ($destination): array => [
                        'id' => $destination->id,
                        'sequence_order' => $destination->sequence_order,
                        'status' => $destination->status,
                        'planned_portion_count' => $destination->planned_portion_count,
                        'delivered_portion_count' => $destination->delivered_portion_count,
                        'location' => [
                            'id' => $destination->location->id,
                            'name' => $destination->location->name,
                            'latitude' => (float) $destination->location->latitude,
                            'longitude' => (float) $destination->location->longitude,
                        ],
                        'recipient' => [
                            'id' => $destination->recipient->id,
                            'name' => $destination->recipient->name,
                        ],
                    ]),
            ]
        ));
    }

    public function routeMap(RoutePlan $routePlan): JsonResponse
    {
        $routePlan->load([
            'run.schedule.depot',
            'run.officer',
            'run.latestOfficerPosition',
            'steps.location',
            'steps.runDestination.recipient',
        ]);

        return response()->json([
            'route' => [
                'id' => $routePlan->id,
                'code' => $routePlan->code,
                'algorithm' => $routePlan->algorithm,
                'total_distance_km' => (float) $routePlan->total_distance_km,
                'total_estimated_minutes' => $routePlan->total_estimated_minutes,
            ],
            'distribution' => $this->runPayload($routePlan->run),
            'steps' => $routePlan->steps->map(fn ($step): array => [
                'order' => $step->step_order,
                'type' => $step->step_type,
                'location' => [
                    'id' => $step->location->id,
                    'name' => $step->location->name,
                    'latitude' => (float) $step->location->latitude,
                    'longitude' => (float) $step->location->longitude,
                ],
                'recipient' => $step->runDestination?->recipient?->name,
                'distance_from_previous_km' => (float) $step->distance_from_previous_km,
                'cumulative_distance_km' => (float) $step->cumulative_distance_km,
            ])->values(),
        ]);
    }

    public function reportSummary(Request $request): JsonResponse
    {
        $runs = DistributionRun::query()
            ->with(['schedule', 'destinations', 'routePlan'])
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('date_from'), fn (Builder $query): Builder => $query->whereHas(
                'schedule',
                fn (Builder $scheduleQuery): Builder => $scheduleQuery->whereDate('scheduled_date', '>=', $request->date('date_from'))
            ))
            ->when($request->filled('date_to'), fn (Builder $query): Builder => $query->whereHas(
                'schedule',
                fn (Builder $scheduleQuery): Builder => $scheduleQuery->whereDate('scheduled_date', '<=', $request->date('date_to'))
            ))
            ->get();

        return response()->json([
            'total_runs' => $runs->count(),
            'completed_runs' => $runs->where('status', 'completed')->count(),
            'planned_portions' => $runs->sum(fn (DistributionRun $run): int => $run->destinations->sum('planned_portion_count')),
            'delivered_portions' => $runs->sum(fn (DistributionRun $run): int => $run->destinations->where('status', 'delivered')->sum('delivered_portion_count')),
            'total_distance_km' => round($runs->sum(fn (DistributionRun $run): float => (float) ($run->routePlan?->total_distance_km ?? 0)), 3),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function runPayload(DistributionRun $run): array
    {
        return [
            'id' => $run->id,
            'code' => $run->code,
            'status' => $run->status,
            'started_at' => $run->started_at?->toIso8601String(),
            'completed_at' => $run->completed_at?->toIso8601String(),
            'scheduled_date' => $run->schedule->scheduled_date->format('Y-m-d'),
            'officer' => [
                'id' => $run->officer->id,
                'name' => $run->officer->name,
            ],
            'depot' => [
                'id' => $run->schedule->depot->id,
                'name' => $run->schedule->depot->name,
                'latitude' => (float) $run->schedule->depot->latitude,
                'longitude' => (float) $run->schedule->depot->longitude,
            ],
            'route_plan_id' => $run->routePlan?->id,
            'latest_position' => $run->latestOfficerPosition ? [
                'latitude' => (float) $run->latestOfficerPosition->latitude,
                'longitude' => (float) $run->latestOfficerPosition->longitude,
                'recorded_at' => $run->latestOfficerPosition->recorded_at->toIso8601String(),
            ] : null,
        ];
    }
}
