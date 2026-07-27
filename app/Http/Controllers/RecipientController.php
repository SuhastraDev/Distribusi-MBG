<?php

namespace App\Http\Controllers;

use App\Http\Requests\Recipient\StoreRecipientRequest;
use App\Http\Requests\Recipient\UpdateRecipientRequest;
use App\Models\Location;
use App\Models\Recipient;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

class RecipientController extends Controller
{
    public function index(): View
    {
        $recipients = Recipient::query()
            ->with('location')
            ->latest()
            ->paginate(10);

        return view('recipients.index', compact('recipients'));
    }

    public function create(): View
    {
        return view('recipients.create', [
            'locations' => $this->activeLocations(),
        ]);
    }

    public function store(StoreRecipientRequest $request): RedirectResponse
    {
        $recipient = Recipient::query()->create($request->validated());

        return redirect()
            ->route('recipients.show', $recipient)
            ->with('status', 'Data penerima MBG berhasil ditambahkan.');
    }

    public function show(Recipient $recipient): View
    {
        $recipient->load('location');

        return view('recipients.show', compact('recipient'));
    }

    public function edit(Recipient $recipient): View
    {
        return view('recipients.edit', [
            'recipient' => $recipient,
            'locations' => $this->activeLocations(),
        ]);
    }

    public function update(UpdateRecipientRequest $request, Recipient $recipient): RedirectResponse
    {
        $recipient->update($request->validated());

        return redirect()
            ->route('recipients.show', $recipient)
            ->with('status', 'Data penerima MBG berhasil diperbarui.');
    }

    public function destroy(Recipient $recipient): RedirectResponse
    {
        $recipient->update(['status' => 'inactive']);

        return redirect()
            ->route('recipients.index')
            ->with('status', 'Penerima MBG berhasil dinonaktifkan.');
    }

    /**
     * @return Collection<int, Location>
     */
    private function activeLocations()
    {
        return Location::query()
            ->active()
            ->orderBy('name')
            ->get();
    }
}
