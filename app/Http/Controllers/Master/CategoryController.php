<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->with('division')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->input('division_id'), function ($query, $divisionId) {
                $query->where('division_id', $divisionId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Master/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'division_id']),
            'divisions' => Division::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'division_id' => 'required|exists:divisions,id',
        ]);
        Category::create($request->only('name', 'division_id'));

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'division_id' => 'required|exists:divisions,id',
        ]);
        $category->update($request->only('name', 'division_id'));

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function show(Category $category)
    {
        //
    }

    public function edit(Category $category)
    {
        //
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }
}
