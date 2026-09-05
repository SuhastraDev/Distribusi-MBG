<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributionRun\StoreDistributionRunRequest;
use App\Http\Requests\DistributionRun\UpdateRunDestinationRequest;
use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\DistributionSchedule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DistributionRunController extends Controller
{
    public function index(): View
    {
        $distributionRuns = DistributionRun::query()
            ->with(['schedule.depot', 'officer'])
            ->latest()
            ->paginate(10);

        return view('distribution-runs.index', compact('distributionRuns'));
    }

    public function create(): View
    {
        $user = request()->user();

        $schedules = DistributionSchedule::query()
            ->where('status', 'scheduled')
            ->whereDoesntHave('runs')
            ->whereHas('destinations')
            ->when(
                ! $user?->hasRole('admin'),
                fn ($query) => $query->where('officer_id', $user?->officer?->id)
            )
            ->with(['officer', 'depot'])
            ->orderBy('scheduled_date')
            ->get();

        return view('distribution-runs.create', compact('schedules'));
    }

    public function store(StoreDistributionRunRequest $request): RedirectResponse
    {
        $schedule = DistributionSchedule::query()
            ->with('destinations')
            ->findOrFail($request->integer('distribution_schedule_id'));

        $this->authorizeScheduleOfficer($schedule);

        if ($schedule->destinations->isEmpty()) {
            throw ValidationException::withMessages([
                'distribution_schedule_id' => 'Jadwal harus memiliki minimal satu tujuan sebelum distribusi dibuat.',
            ]);
        }

        $run = DB::transaction(function () use ($request, $schedule): DistributionRun {
            $run = DistributionRun::query()->create([
                'code' => $this->generateCode(),
                'distribution_schedule_id' => $schedule->id,
                'officer_id' => $schedule->officer_id,
                'status' => 'ready',
                'notes' => $request->string('notes')->toString() ?: null,
            ]);

            foreach ($schedule->destinations->sortBy('sequence_order') as $destination) {
                $run->destinations()->create([
                    'distribution_schedule_destination_id' => $destination->id,
                    'location_id' => $destination->location_id,
                    'recipient_id' => $destination->recipient_id,
                    'planned_portion_count' => $destination->portion_count,
                    'sequence_order' => $destination->sequence_order,
                    'status' => 'pending',
                ]);
            }

            return $run;
        });

        return redirect()
            ->route('distribution-runs.show', $run)
            ->with('status', 'Distribusi aktual berhasil dibuat dari jadwal.');
    }

    public function show(DistributionRun $distributionRun): View
    {
        $distributionRun->load([
            'schedule.depot',
            'officer.user',
            'latestOfficerPosition',
            'destinations.location',
            'destinations.recipient',
            'routePlan.steps.location',
        ]);

        return view('distribution-runs.show', compact('distributionRun'));
    }

    public function start(DistributionRun $distributionRun): RedirectResponse
    {
        $this->authorizeRunOfficer($distributionRun);

        abort_unless($distributionRun->canBeStarted(), 422, 'Distribusi hanya bisa dimulai dari status ready.');

        $distributionRun->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return back()->with('status', 'Distribusi mulai berjalan.');
    }

    public function updateDestination(
        UpdateRunDestinationRequest $request,
        DistributionRun $distributionRun,
        DistributionRunDestination $destination
    ): RedirectResponse {
        $this->authorizeRunOfficer($distributionRun);
        abort_unless($destination->distribution_run_id === $distributionRun->id, 404);
        abort_unless($distributionRun->status === 'in_progress', 422, 'Tujuan hanya bisa diperbarui saat distribusi berjalan.');

        $status = $request->string('status')->toString();
        $deliveredPortions = $status === 'delivered'
            ? $request->integer('delivered_portion_count')
            : null;

        if ($deliveredPortions !== null && $deliveredPortions > $destination->planned_portion_count) {
            throw ValidationException::withMessages([
                'delivered_portion_count' => 'Porsi terkirim tidak boleh melebihi porsi rencana.',
            ]);
        }

        $destination->update([
            'status' => $status,
            'delivered_portion_count' => $deliveredPortions,
            'arrived_at' => $status === 'arrived' ? now() : $destination->arrived_at,
            'delivered_at' => $status === 'delivered' ? now() : null,
            'proof_notes' => $request->string('proof_notes')->toString() ?: null,
        ]);

        return back()->with('status', 'Status tujuan distribusi berhasil diperbarui.');
    }

    public function complete(DistributionRun $distributionRun): RedirectResponse
    {
        $this->authorizeRunOfficer($distributionRun);

        abort_unless($distributionRun->canBeCompleted(), 422, 'Semua tujuan harus delivered atau skipped sebelum distribusi selesai.');

        $distributionRun->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('status', 'Distribusi berhasil diselesaikan.');
    }

    public function cancel(DistributionRun $distributionRun): RedirectResponse
    {
        $this->authorizeRunOfficer($distributionRun);

        abort_if($distributionRun->status === 'completed', 422, 'Distribusi selesai tidak bisa dibatalkan.');

        $distributionRun->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        return back()->with('status', 'Distribusi berhasil dibatalkan.');
    }

    private function generateCode(): string
    {
        do {
            $code = 'RUN-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (DistributionRun::query()->where('code', $code)->exists());

        return $code;
    }

    private function authorizeScheduleOfficer(DistributionSchedule $schedule): void
    {
        $user = request()->user();

        if ($user?->hasRole('admin')) {
            return;
        }

        abort_unless($user?->officer?->id === $schedule->officer_id, 403);
    }

    /**
     * Start/Complete/Cancel and destination status updates are field actions:
     * exclusive to the officer actually assigned to this run. Unlike route
     * generation, admin does NOT get an override bypass here - admin isn't the
     * one physically delivering, so admin triggering "Start"/"Complete" would
     * misrepresent who actually departed.
     */
    private function authorizeRunOfficer(DistributionRun $distributionRun): void
    {
        $user = request()->user();

        abort_unless($user?->officer?->id === $distributionRun->officer_id, 403);
    }
}
