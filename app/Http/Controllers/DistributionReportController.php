<?php

namespace App\Http\Controllers;

use App\Models\DistributionRun;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DistributionReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request)
            ->with(['schedule.depot', 'officer', 'routePlan', 'destinations']);

        $summaryRuns = (clone $query)->get();
        $distributionRuns = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reports.distributions', [
            'distributionRuns' => $distributionRuns,
            'summary' => $this->summary($summaryRuns),
            'filters' => $request->only(['date_from', 'date_to', 'status']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $distributionRuns = $this->filteredQuery($request)
            ->with(['schedule.depot', 'officer', 'routePlan', 'destinations'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($distributionRuns): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Kode Distribusi',
                'Tanggal Jadwal',
                'Petugas',
                'Depot',
                'Status',
                'Total Tujuan',
                'Tujuan Terkirim',
                'Porsi Rencana',
                'Porsi Terkirim',
                'Jarak Rute KM',
                'Estimasi Menit',
            ]);

            foreach ($distributionRuns as $run) {
                fputcsv($handle, [
                    $run->code,
                    $run->schedule->scheduled_date->format('Y-m-d'),
                    $run->officer->name,
                    $run->schedule->depot->name,
                    $run->status,
                    $run->destinations->count(),
                    $run->destinations->where('status', 'delivered')->count(),
                    $run->destinations->sum('planned_portion_count'),
                    $run->destinations->where('status', 'delivered')->sum('delivered_portion_count'),
                    $run->routePlan?->total_distance_km ?? 0,
                    $run->routePlan?->total_estimated_minutes ?? 0,
                ]);
            }

            fclose($handle);
        }, 'laporan-distribusi.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return Builder<DistributionRun>
     */
    private function filteredQuery(Request $request): Builder
    {
        return DistributionRun::query()
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('date_from'), fn (Builder $query): Builder => $query->whereHas(
                'schedule',
                fn (Builder $scheduleQuery): Builder => $scheduleQuery->whereDate('scheduled_date', '>=', $request->date('date_from'))
            ))
            ->when($request->filled('date_to'), fn (Builder $query): Builder => $query->whereHas(
                'schedule',
                fn (Builder $scheduleQuery): Builder => $scheduleQuery->whereDate('scheduled_date', '<=', $request->date('date_to'))
            ));
    }

    /**
     * @param  Collection<int, DistributionRun>  $distributionRuns
     * @return array<string, int|float>
     */
    private function summary(Collection $distributionRuns): array
    {
        return [
            'total_runs' => $distributionRuns->count(),
            'ready_runs' => $distributionRuns->where('status', 'ready')->count(),
            'in_progress_runs' => $distributionRuns->where('status', 'in_progress')->count(),
            'completed_runs' => $distributionRuns->where('status', 'completed')->count(),
            'cancelled_runs' => $distributionRuns->where('status', 'cancelled')->count(),
            'total_destinations' => $distributionRuns->sum(fn (DistributionRun $run): int => $run->destinations->count()),
            'delivered_destinations' => $distributionRuns->sum(fn (DistributionRun $run): int => $run->destinations->where('status', 'delivered')->count()),
            'planned_portions' => $distributionRuns->sum(fn (DistributionRun $run): int => $run->destinations->sum('planned_portion_count')),
            'delivered_portions' => $distributionRuns->sum(fn (DistributionRun $run): int => $run->destinations->where('status', 'delivered')->sum('delivered_portion_count')),
            'total_distance_km' => round($distributionRuns->sum(fn (DistributionRun $run): float => (float) ($run->routePlan?->total_distance_km ?? 0)), 3),
        ];
    }
}
