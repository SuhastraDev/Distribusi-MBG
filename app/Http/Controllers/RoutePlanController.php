<?php

namespace App\Http\Controllers;

use App\Models\DistributionRun;
use App\Models\RoutePlan;
use App\Services\GreedyRouteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RoutePlanController extends Controller
{
    public function index(): View
    {
        $routePlans = RoutePlan::query()
            ->with(['run.schedule', 'run.officer'])
            ->latest()
            ->paginate(10);

        return view('route-plans.index', compact('routePlans'));
    }

    public function show(RoutePlan $routePlan): View
    {
        $routePlan->load([
            'run.schedule.depot',
            'run.officer',
            'run.latestOfficerPosition',
            'steps.location',
            'steps.runDestination.recipient',
        ]);

        return view('route-plans.show', compact('routePlan'));
    }

    public function mapData(RoutePlan $routePlan): JsonResponse
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
                'status' => $routePlan->status,
            ],
            'distribution' => [
                'code' => $routePlan->run->code,
                'status' => $routePlan->run->status,
                'officer' => $routePlan->run->officer->name,
                'scheduled_date' => $routePlan->run->schedule->scheduled_date->format('Y-m-d'),
            ],
            'center' => [
                'latitude' => (float) $routePlan->run->schedule->depot->latitude,
                'longitude' => (float) $routePlan->run->schedule->depot->longitude,
            ],
            'officer_position' => $routePlan->run->latestOfficerPosition ? [
                'latitude' => (float) $routePlan->run->latestOfficerPosition->latitude,
                'longitude' => (float) $routePlan->run->latestOfficerPosition->longitude,
                'accuracy_meters' => $routePlan->run->latestOfficerPosition->accuracy_meters === null
                    ? null
                    : (float) $routePlan->run->latestOfficerPosition->accuracy_meters,
                'recorded_at' => $routePlan->run->latestOfficerPosition->recorded_at->toIso8601String(),
            ] : null,
            'steps' => $routePlan->steps->map(fn ($step): array => [
                'order' => $step->step_order,
                'type' => $step->step_type,
                'location' => [
                    'id' => $step->location->id,
                    'name' => $step->location->name,
                    'address' => $step->location->address,
                    'latitude' => (float) $step->location->latitude,
                    'longitude' => (float) $step->location->longitude,
                ],
                'recipient' => $step->runDestination?->recipient?->name,
                'destination_status' => $step->runDestination?->status,
                'planned_portions' => $step->runDestination?->planned_portion_count,
                'distance_from_previous_km' => (float) $step->distance_from_previous_km,
                'cumulative_distance_km' => (float) $step->cumulative_distance_km,
            ])->values(),
        ]);
    }

    public function generate(DistributionRun $distributionRun, GreedyRouteService $service): RedirectResponse
    {
        $this->authorizeRunOfficer($distributionRun);

        $routePlan = $service->generate($distributionRun);

        return redirect()
            ->route('route-plans.show', $routePlan)
            ->with('status', 'Rute distribusi berhasil dibuat dengan algoritma Greedy.');
    }

    private function authorizeRunOfficer(DistributionRun $distributionRun): void
    {
        $user = request()->user();

        if ($user?->hasRole('admin') || $user?->hasRole('kepala_sppg')) {
            return;
        }

        abort_unless($user?->officer?->id === $distributionRun->officer_id, 403);
    }
}
