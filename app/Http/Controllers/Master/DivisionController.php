<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DivisionController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Master/Divisions/Index', [
            'divisions' => $divisions,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:divisions']);
        Division::create($request->only('name'));

        return redirect()->back()->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function show(Division $division)
    {
        //
    }

    public function edit(Division $division)
    {
        //
    }

    public function update(Request $request, Division $division)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories,name,'.$division->id]);
        $division->update($request->only('name'));

        return redirect()->back()->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Division $division)
    {
        $division->delete();

        return redirect()->back()->with('success', 'Divisi berhasil dihapus.');
    }
}
