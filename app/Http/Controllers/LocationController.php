<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::query()
            ->latest()
            ->paginate(10);

        $mapLocations = Location::query()
            ->active()
            ->orderBy('type')
            ->get();

        return view('locations.index', compact('locations', 'mapLocations'));
    }

    public function create(): View
    {
        return view('locations.create');
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $location = Location::query()->create($request->validated());

        return redirect()
            ->route('locations.show', $location)
            ->with('status', 'Data lokasi berhasil ditambahkan.');
    }

    public function show(Location $location): View
    {
        return view('locations.show', compact('location'));
    }

    public function edit(Location $location): View
    {
        return view('locations.edit', compact('location'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()
            ->route('locations.show', $location)
            ->with('status', 'Data lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->update(['status' => 'inactive']);

        return redirect()
            ->route('locations.index')
            ->with('status', 'Lokasi berhasil dinonaktifkan.');
    }
}
