<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfficerPosition\StoreOfficerPositionRequest;
use App\Models\DistributionRun;
use App\Models\OfficerPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class OfficerPositionController extends Controller
{
    public function store(StoreOfficerPositionRequest $request, DistributionRun $distributionRun): RedirectResponse
    {
        $this->authorizeRunOfficer($distributionRun);
        $this->ensureRunCanReceivePosition($distributionRun);

        OfficerPosition::query()->create([
            'distribution_run_id' => $distributionRun->id,
            'officer_id' => $distributionRun->officer_id,
            'latitude' => $request->float('latitude'),
            'longitude' => $request->float('longitude'),
            'accuracy_meters' => $request->filled('accuracy_meters') ? $request->float('accuracy_meters') : null,
            'recorded_at' => $request->date('recorded_at') ?? now(),
        ]);

        return back()->with('status', 'Posisi petugas berhasil diperbarui.');
    }

    public function latest(DistributionRun $distributionRun): JsonResponse
    {
        $distributionRun->load(['officer', 'latestOfficerPosition']);

        $position = $distributionRun->latestOfficerPosition;

        return response()->json([
            'distribution' => [
                'id' => $distributionRun->id,
                'code' => $distributionRun->code,
                'status' => $distributionRun->status,
            ],
            'officer' => [
                'id' => $distributionRun->officer->id,
                'name' => $distributionRun->officer->name,
            ],
            'position' => $position ? [
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
                'accuracy_meters' => $position->accuracy_meters === null ? null : (float) $position->accuracy_meters,
                'recorded_at' => $position->recorded_at->toIso8601String(),
            ] : null,
        ]);
    }

    private function authorizeRunOfficer(DistributionRun $distributionRun): void
    {
        $user = request()->user();

        if ($user?->hasRole('admin')) {
            return;
        }

        abort_unless($user?->officer?->id === $distributionRun->officer_id, 403);
    }

    private function ensureRunCanReceivePosition(DistributionRun $distributionRun): void
    {
        abort_unless($distributionRun->status === 'in_progress', 422, 'Posisi hanya bisa diperbarui saat distribusi berjalan.');
    }
}
