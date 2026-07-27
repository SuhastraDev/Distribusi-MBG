<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributionSchedule\StoreDistributionScheduleRequest;
use App\Http\Requests\DistributionSchedule\StoreScheduleDestinationRequest;
use App\Http\Requests\DistributionSchedule\UpdateDistributionScheduleRequest;
use App\Models\DistributionSchedule;
use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Officer;
use App\Models\Recipient;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DistributionScheduleController extends Controller
{
    public function index(): View
    {
        $distributionSchedules = DistributionSchedule::query()
            ->with(['officer', 'depot'])
            ->latest('scheduled_date')
            ->paginate(10);

        return view('distribution-schedules.index', compact('distributionSchedules'));
    }

    public function create(): View
    {
        return view('distribution-schedules.create', $this->formData());
    }

    public function store(StoreDistributionScheduleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->assertRecipientsMatchRules(
            (int) $validated['depot_location_id'],
            $validated['recipient_ids']
        );

        $schedule = DB::transaction(function () use ($validated): DistributionSchedule {
            $schedule = DistributionSchedule::query()->create([
                'code' => $validated['code'],
                'scheduled_date' => $validated['scheduled_date'],
                'officer_id' => $validated['officer_id'],
                'depot_location_id' => $validated['depot_location_id'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncDestinations($schedule, $validated['recipient_ids']);

            return $schedule;
        });

        return redirect()
            ->route('distribution-schedules.show', $schedule)
            ->with('status', 'Jadwal distribusi berhasil ditambahkan.');
    }

    public function show(DistributionSchedule $distributionSchedule): View
    {
        $distributionSchedule->load(['officer.user', 'depot', 'destinations.location', 'destinations.recipient']);

        return view('distribution-schedules.show', array_merge(
            compact('distributionSchedule'),
            ['recipients' => $this->activeRecipients()]
        ));
    }

    public function edit(DistributionSchedule $distributionSchedule): View
    {
        $distributionSchedule->load('destinations');

        return view('distribution-schedules.edit', array_merge(
            compact('distributionSchedule'),
            $this->formData()
        ));
    }

    public function update(UpdateDistributionScheduleRequest $request, DistributionSchedule $distributionSchedule): RedirectResponse
    {
        $validated = $request->validated();

        $this->assertRecipientsMatchRules(
            (int) $validated['depot_location_id'],
            $validated['recipient_ids']
        );

        DB::transaction(function () use ($distributionSchedule, $validated): void {
            $distributionSchedule->update([
                'code' => $validated['code'],
                'scheduled_date' => $validated['scheduled_date'],
                'officer_id' => $validated['officer_id'],
                'depot_location_id' => $validated['depot_location_id'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncDestinations($distributionSchedule, $validated['recipient_ids']);
        });

        return redirect()
            ->route('distribution-schedules.show', $distributionSchedule)
            ->with('status', 'Jadwal distribusi berhasil diperbarui.');
    }

    public function destroy(DistributionSchedule $distributionSchedule): RedirectResponse
    {
        $distributionSchedule->update(['status' => 'cancelled']);

        return redirect()
            ->route('distribution-schedules.index')
            ->with('status', 'Jadwal distribusi berhasil dibatalkan.');
    }

    public function storeDestination(StoreScheduleDestinationRequest $request, DistributionSchedule $distributionSchedule): RedirectResponse
    {
        $recipient = Recipient::query()
            ->active()
            ->with('location')
            ->findOrFail($request->integer('recipient_id'));

        $this->assertRecipientCanBeAdded($distributionSchedule, $recipient);

        DistributionScheduleDestination::query()->create([
            'distribution_schedule_id' => $distributionSchedule->id,
            'location_id' => $recipient->location_id,
            'recipient_id' => $recipient->id,
            'portion_count' => $recipient->portion_count,
            'sequence_order' => $distributionSchedule->destinations()->count() + 1,
        ]);

        $distributionSchedule->recalculateTotalPortions();

        return back()->with('status', 'Tujuan jadwal berhasil ditambahkan.');
    }

    public function destroyDestination(
        DistributionSchedule $distributionSchedule,
        DistributionScheduleDestination $destination
    ): RedirectResponse {
        abort_unless($destination->distribution_schedule_id === $distributionSchedule->id, 404);

        $destination->delete();
        $distributionSchedule->recalculateTotalPortions();

        return back()->with('status', 'Tujuan jadwal berhasil dihapus.');
    }

    /**
     * @return array<string, Collection<int, mixed>>
     */
    private function formData(): array
    {
        return [
            'officers' => Officer::query()->active()->orderBy('name')->get(),
            'depots' => Location::query()->active()->where('type', 'depot')->orderBy('name')->get(),
            'recipients' => $this->activeRecipients(),
        ];
    }

    /**
     * @return Collection<int, Recipient>
     */
    private function activeRecipients(): Collection
    {
        return Recipient::query()
            ->active()
            ->with('location')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, int|string>  $recipientIds
     */
    private function syncDestinations(DistributionSchedule $schedule, array $recipientIds): void
    {
        $schedule->destinations()->delete();

        $recipients = Recipient::query()
            ->active()
            ->with('location')
            ->whereIn('id', $recipientIds)
            ->get()
            ->keyBy('id');

        foreach (array_values($recipientIds) as $index => $recipientId) {
            $recipient = $recipients->get((int) $recipientId);

            DistributionScheduleDestination::query()->create([
                'distribution_schedule_id' => $schedule->id,
                'location_id' => $recipient->location_id,
                'recipient_id' => $recipient->id,
                'portion_count' => $recipient->portion_count,
                'sequence_order' => $index + 1,
            ]);
        }

        $schedule->recalculateTotalPortions();
    }

    /**
     * @param  array<int, int|string>  $recipientIds
     */
    private function assertRecipientsMatchRules(int $depotLocationId, array $recipientIds): void
    {
        $recipients = Recipient::query()
            ->active()
            ->with('location')
            ->whereIn('id', $recipientIds)
            ->get();

        if ($recipients->count() !== count($recipientIds)) {
            throw ValidationException::withMessages([
                'recipient_ids' => 'Semua tujuan harus berasal dari penerima aktif pada lokasi aktif.',
            ]);
        }

        $locationIds = $recipients->pluck('location_id');

        if ($locationIds->contains($depotLocationId)) {
            throw ValidationException::withMessages([
                'recipient_ids' => 'Depot tidak boleh dipilih sebagai tujuan distribusi.',
            ]);
        }

        if ($locationIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'recipient_ids' => 'Tujuan lokasi tidak boleh duplikat dalam satu jadwal.',
            ]);
        }
    }

    private function assertRecipientCanBeAdded(DistributionSchedule $schedule, Recipient $recipient): void
    {
        if ($recipient->location_id === $schedule->depot_location_id) {
            throw ValidationException::withMessages([
                'recipient_id' => 'Depot tidak boleh dipilih sebagai tujuan distribusi.',
            ]);
        }

        if ($schedule->destinations()->where('location_id', $recipient->location_id)->exists()) {
            throw ValidationException::withMessages([
                'recipient_id' => 'Tujuan lokasi tidak boleh duplikat dalam satu jadwal.',
            ]);
        }
    }
}
