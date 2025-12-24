<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StationController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $this->currentStoreId();
        $query = Station::query()
            ->where('store_id', $storeId);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('device_identifier', 'like', '%' . $search . '%');
            });
        }

        $stations = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Stations/Index', [
            'stations' => $stations,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'device_identifier' => ['required', 'string', 'max:255', 'unique:stations,device_identifier'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        Station::create([
            'store_id' => $this->currentStoreId(),
            'name' => $validated['name'],
            'device_identifier' => $validated['device_identifier'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('stations.index')
            ->with('success', 'Station berhasil ditambahkan.');
    }

    public function update(Request $request, Station $station)
    {
        if ($station->store_id !== $this->currentStoreId()) {
            return back()->with('error', 'Station tidak valid.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'device_identifier' => ['required', 'string', 'max:255', 'unique:stations,device_identifier,' . $station->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $station->update([
            'name' => $validated['name'],
            'device_identifier' => $validated['device_identifier'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('stations.index')
            ->with('success', 'Station berhasil diperbarui.');
    }

    public function destroy(Station $station)
    {
        if ($station->store_id !== $this->currentStoreId()) {
            return back()->with('error', 'Station tidak valid.');
        }

        $station->delete();

        return redirect()->route('stations.index')
            ->with('success', 'Station berhasil dihapus.');
    }
}
