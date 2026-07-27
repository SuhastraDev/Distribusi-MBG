<?php

namespace App\Http\Controllers;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\Officer;
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
            'filters' => $request->only(['date_from', 'date_to', 'officer_id', 'status']),
            'officers' => Officer::query()->orderBy('name')->get(),
        ]);
    }

    public function show(DistributionRun $distributionRun): View
    {
        $distributionRun->load([
            'schedule.depot',
            'officer.user',
            'routePlan',
            'destinations.location',
            'destinations.recipient',
        ]);

        return view('reports.distribution-detail', [
            'run' => $distributionRun,
            'metrics' => $this->runMetrics($distributionRun),
            'statusTimeline' => $this->statusTimeline($distributionRun),
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
                'Waktu Aktual Menit',
            ]);

            foreach ($distributionRuns as $run) {
                $metrics = $this->runMetrics($run);

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
                    $metrics['actual_duration_minutes'],
                ]);
            }

            fclose($handle);
        }, 'laporan-distribusi.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $distributionRuns = $this->filteredQuery($request)
            ->with(['schedule.depot', 'officer', 'routePlan', 'destinations'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($distributionRuns): void {
            echo '<table border="1">';
            echo '<thead><tr>';

            foreach ([
                'Kode Distribusi',
                'Tanggal Jadwal',
                'Petugas',
                'Depot',
                'Status Akhir',
                'Total Tujuan',
                'Tujuan Terkirim',
                'Porsi Rencana',
                'Porsi Terkirim',
                'Jarak Rute KM',
                'Estimasi Menit',
                'Waktu Aktual Menit',
            ] as $heading) {
                echo '<th>'.e($heading).'</th>';
            }

            echo '</tr></thead><tbody>';

            foreach ($distributionRuns as $run) {
                $metrics = $this->runMetrics($run);

                echo '<tr>';
                foreach ([
                    $run->code,
                    $run->schedule->scheduled_date->format('Y-m-d'),
                    $run->officer->name,
                    $run->schedule->depot->name,
                    $run->status,
                    $metrics['total_destinations'],
                    $metrics['delivered_destinations'],
                    $metrics['planned_portions'],
                    $metrics['delivered_portions'],
                    $metrics['total_distance_km'],
                    $metrics['estimated_minutes'],
                    $metrics['actual_duration_minutes'],
                ] as $cell) {
                    echo '<td>'.e((string) $cell).'</td>';
                }
                echo '</tr>';
            }

            echo '</tbody></table>';
        }, 'laporan-distribusi.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    /**
     * @return Builder<DistributionRun>
     */
    private function filteredQuery(Request $request): Builder
    {
        return DistributionRun::query()
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('officer_id'), fn (Builder $query): Builder => $query->where('officer_id', $request->integer('officer_id')))
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

    /**
     * @return array<string, int|float|null>
     */
    private function runMetrics(DistributionRun $run): array
    {
        return [
            'total_destinations' => $run->destinations->count(),
            'delivered_destinations' => $run->destinations->where('status', 'delivered')->count(),
            'failed_destinations' => $run->destinations->where('status', 'failed')->count(),
            'planned_portions' => $run->destinations->sum('planned_portion_count'),
            'delivered_portions' => $run->destinations->where('status', 'delivered')->sum('delivered_portion_count'),
            'total_distance_km' => (float) ($run->routePlan?->total_distance_km ?? 0),
            'estimated_minutes' => $run->routePlan?->total_estimated_minutes,
            'actual_duration_minutes' => $run->started_at && $run->completed_at
                ? $run->started_at->diffInMinutes($run->completed_at)
                : null,
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function statusTimeline(DistributionRun $run): array
    {
        $timeline = [
            [
                'label' => 'Distribusi dibuat',
                'status' => 'ready',
                'time' => $run->created_at?->format('d/m/Y H:i'),
                'notes' => $run->notes,
            ],
        ];

        if ($run->started_at) {
            $timeline[] = [
                'label' => 'Distribusi dimulai',
                'status' => 'in_progress',
                'time' => $run->started_at->format('d/m/Y H:i'),
                'notes' => null,
            ];
        }

        $run->destinations
            ->sortBy('sequence_order')
            ->each(function (DistributionRunDestination $destination) use (&$timeline): void {
                if ($destination->arrived_at) {
                    $timeline[] = [
                        'label' => 'Tiba di '.$destination->location->name,
                        'status' => 'arrived',
                        'time' => $destination->arrived_at->format('d/m/Y H:i'),
                        'notes' => $destination->proof_notes,
                    ];
                }

                if ($destination->delivered_at) {
                    $timeline[] = [
                        'label' => 'Tujuan selesai: '.$destination->location->name,
                        'status' => $destination->status,
                        'time' => $destination->delivered_at->format('d/m/Y H:i'),
                        'notes' => $destination->proof_notes,
                    ];
                }
            });

        if ($run->completed_at) {
            $timeline[] = [
                'label' => $run->status === 'cancelled' ? 'Distribusi dibatalkan' : 'Distribusi selesai',
                'status' => $run->status,
                'time' => $run->completed_at->format('d/m/Y H:i'),
                'notes' => $run->notes,
            ];
        }

        return $timeline;
    }
}
