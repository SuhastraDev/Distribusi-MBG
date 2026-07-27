<?php

namespace App\Http\Controllers;

use App\Models\DistributionRun;
use App\Models\RoutePlan;
use App\Services\GreedyRouteService;
use Illuminate\Contracts\View\View;
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
            'steps.location',
            'steps.runDestination.recipient',
        ]);

        return view('route-plans.show', compact('routePlan'));
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
