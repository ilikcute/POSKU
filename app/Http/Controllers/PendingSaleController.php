<?php

namespace App\Http\Controllers;

use App\Models\PendingSale;
use App\Services\StationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingSaleController extends Controller
{
    public function index(Request $request)
    {
        $station = StationResolver::resolve();

        $pending = PendingSale::query()
            ->where('station_id', $station->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($pending);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payload' => ['required', 'array'],
        ]);

        $station = StationResolver::resolve();

        $pending = PendingSale::create([
            'station_id' => $station->id,
            'user_id' => Auth::id(),
            'payload' => $validated['payload'],
        ]);

        return response()->json($pending, 201);
    }

    public function destroy(PendingSale $pendingSale)
    {
        $station = StationResolver::resolve();
        if ($pendingSale->station_id !== $station->id) {
            abort(403);
        }

        $pendingSale->delete();

        return response()->json(['ok' => true]);
    }
}
