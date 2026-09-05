<?php

namespace App\Http\Controllers;

use App\Http\Requests\Officer\StoreOfficerRequest;
use App\Http\Requests\Officer\UpdateOfficerRequest;
use App\Models\DistributionRun;
use App\Models\DistributionSchedule;
use App\Models\Officer;
use App\Models\OfficerPosition;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class OfficerController extends Controller
{
    public function index(): View
    {
        $officers = Officer::query()
            ->with('user.role')
            ->latest()
            ->paginate(10);

        return view('officers.index', compact('officers'));
    }

    public function create(): View
    {
        return view('officers.create');
    }

    public function store(StoreOfficerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $officer = DB::transaction(function () use ($validated): Officer {
            $role = Role::query()
                ->where('name', 'petugas')
                ->firstOrFail();

            $user = User::query()->create([
                'role_id' => $role->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'password' => $validated['password'],
            ]);

            return Officer::query()->create([
                'user_id' => $user->id,
                'officer_code' => $validated['officer_code'],
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('officers.show', $officer)
            ->with('status', 'Data petugas berhasil ditambahkan.');
    }

    public function show(Officer $officer): View
    {
        $officer->load('user.role');

        return view('officers.show', compact('officer'));
    }

    public function edit(Officer $officer): View
    {
        $officer->load('user');

        return view('officers.edit', compact('officer'));
    }

    public function update(UpdateOfficerRequest $request, Officer $officer): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($officer, $validated): void {
            $officer->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);

            if (! empty($validated['password'])) {
                $officer->user->update([
                    'password' => $validated['password'],
                ]);
            }

            $officer->update([
                'officer_code' => $validated['officer_code'],
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('officers.show', $officer)
            ->with('status', 'Data petugas berhasil diperbarui.');
    }

    public function destroy(Officer $officer): RedirectResponse
    {
        DB::transaction(function () use ($officer): void {
            $officer->update(['status' => 'inactive']);
            $officer->user->update(['status' => 'inactive']);
        });

        return redirect()
            ->route('officers.index')
            ->with('status', 'Petugas berhasil dinonaktifkan.');
    }

    /**
     * Permanently remove an officer and their login account. Only allowed
     * when the officer has no schedule/run/position history - deleting an
     * officer who already has field activity would orphan that history's
     * foreign keys (the database itself would reject it).
     */
    public function forceDestroy(Officer $officer): RedirectResponse
    {
        $blockers = array_filter([
            DistributionSchedule::query()->where('officer_id', $officer->id)->exists() ? 'jadwal distribusi' : null,
            DistributionRun::query()->where('officer_id', $officer->id)->exists() ? 'distribusi aktual' : null,
            OfficerPosition::query()->where('officer_id', $officer->id)->exists() ? 'riwayat posisi GPS' : null,
        ]);

        if (! empty($blockers)) {
            return back()->with('error', 'Petugas tidak bisa dihapus permanen karena masih memiliki: '.implode(', ', $blockers).'. Gunakan "Nonaktifkan" saja.');
        }

        DB::transaction(function () use ($officer): void {
            $user = $officer->user;
            $officer->delete();
            $user?->delete();
        });

        return redirect()
            ->route('officers.index')
            ->with('status', 'Petugas berhasil dihapus permanen.');
    }
}
