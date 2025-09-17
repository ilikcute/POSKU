<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RackController extends Controller
{
    public function index(Request $request)
    {
        $racks = Rack::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Master/Racks/Index', [
            'racks' => $racks,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:racks']);
        Rack::create($request->only('name'));

        return redirect()->back()->with('success', 'Rack berhasil ditambahkan.');
    }

    public function show(Rack $rack)
    {
        //
    }

    public function edit(Rack $rack)
    {
        //
    }

    public function update(Request $request, Rack $rack)
    {
        $request->validate(['name' => 'required|string|max:255|unique:racks,name,'.$rack->id]);
        $rack->update($request->only('name'));

        return redirect()->back()->with('success', 'Rack berhasil diperbarui.');
    }

    public function destroy(Rack $rack)
    {
        $rack->delete();

        return redirect()->back()->with('success', 'Rack berhasil dihapus.');
    }
}
