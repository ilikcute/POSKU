<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Rack;
use App\Models\RackShelf;
use App\Models\RackShelfProduct;
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

    public function planogram()
    {
        $racks = Rack::query()
            ->orderBy('rack_code')
            ->orderBy('name')
            ->get();

        foreach ($racks as $rack) {
            $count = (int) ($rack->shelf_count ?? 0);
            if ($count > 0 && $rack->shelves()->count() !== $count) {
                $this->syncShelves($rack, $count);
            }
        }

        $racks = Rack::query()
            ->with(['shelves.products:id,product_code,name,image'])
            ->orderBy('rack_code')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->select(['id', 'product_code', 'name'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Master/Racks/Planogram', [
            'racks' => $racks,
            'products' => $products,
        ]);
    }

    public function planogramReport()
    {
        $racks = Rack::query()
            ->with(['shelves.products:id,product_code,name,image'])
            ->orderBy('rack_code')
            ->orderBy('name')
            ->get();

        return Inertia::render('Master/Racks/PlanogramReport', [
            'racks' => $racks,
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:racks',
            'rack_code' => 'nullable|string|max:50|unique:racks,rack_code',
            'rack_type' => 'nullable|string|max:50',
            'shelf_count' => 'nullable|integer|min:1|max:50',
        ]);

        $validated['shelf_plan'] = $this->buildShelfPlan((int) ($validated['shelf_count'] ?? 0));
        $rack = Rack::create($validated);
        $this->syncShelves($rack, (int) ($validated['shelf_count'] ?? 0));

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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:racks,name,'.$rack->id,
            'rack_code' => 'nullable|string|max:50|unique:racks,rack_code,'.$rack->id,
            'rack_type' => 'nullable|string|max:50',
            'shelf_count' => 'nullable|integer|min:1|max:50',
        ]);

        $validated['shelf_plan'] = $this->syncShelfPlan(
            $rack->shelf_plan ?? [],
            (int) ($validated['shelf_count'] ?? 0)
        );

        $rack->update($validated);
        $this->syncShelves($rack, (int) ($validated['shelf_count'] ?? 0));

        return redirect()->back()->with('success', 'Rack berhasil diperbarui.');
    }

    public function updatePlanogram(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'shelf_plan' => 'required|array',
            'shelf_plan.*.shelf_id' => 'required|integer|exists:rack_shelves,id',
            'shelf_plan.*.product_ids' => 'array',
            'shelf_plan.*.product_ids.*' => 'integer|exists:products,id',
        ]);

        $rackShelfIds = $rack->shelves()->pluck('id')->all();
        $payload = collect($validated['shelf_plan'])->map(function ($shelf) {
            return [
                'shelf_id' => (int) $shelf['shelf_id'],
                'product_ids' => array_values(array_unique($shelf['product_ids'] ?? [])),
            ];
        });

        foreach ($payload as $shelfData) {
            if (! in_array($shelfData['shelf_id'], $rackShelfIds, true)) {
                return redirect()->back()->withErrors(['shelf_plan' => 'Shelf tidak sesuai rack.']);
            }
        }

        foreach ($payload as $shelfData) {
            RackShelfProduct::where('rack_shelf_id', $shelfData['shelf_id'])->delete();
            $position = 1;
            foreach ($shelfData['product_ids'] as $productId) {
                RackShelfProduct::create([
                    'rack_shelf_id' => $shelfData['shelf_id'],
                    'product_id' => $productId,
                    'position' => $position,
                ]);
                $position++;
            }
        }

        $rack->update([
            'shelf_plan' => $this->buildShelfPlan((int) ($rack->shelf_count ?? 0)),
        ]);

        return redirect()->back()->with('success', 'Planogram rack berhasil diperbarui.');
    }

    public function destroy(Rack $rack)
    {
        $rack->delete();

        return redirect()->back()->with('success', 'Rack berhasil dihapus.');
    }

    private function buildShelfPlan(int $count): array
    {
        if ($count < 1) {
            return [];
        }

        $plan = [];
        for ($i = 1; $i <= $count; $i++) {
            $plan[] = [
                'shelf_no' => $i,
                'product_ids' => [],
            ];
        }

        return $plan;
    }

    private function syncShelfPlan(array $existing, int $count): array
    {
        if ($count < 1) {
            return [];
        }

        $existingMap = [];
        foreach ($existing as $shelf) {
            if (! isset($shelf['shelf_no'])) {
                continue;
            }
            $existingMap[(int) $shelf['shelf_no']] = [
                'shelf_no' => (int) $shelf['shelf_no'],
                'product_ids' => array_values(array_unique($shelf['product_ids'] ?? [])),
            ];
        }

        $plan = [];
        for ($i = 1; $i <= $count; $i++) {
            $plan[] = $existingMap[$i] ?? [
                'shelf_no' => $i,
                'product_ids' => [],
            ];
        }

        return $plan;
    }

    private function syncShelves(Rack $rack, int $count): void
    {
        if ($count < 1) {
            $rack->shelves()->delete();
            return;
        }

        $existing = $rack->shelves()->get()->keyBy('shelf_no');
        for ($i = 1; $i <= $count; $i++) {
            if (! $existing->has($i)) {
                RackShelf::create([
                    'rack_id' => $rack->id,
                    'shelf_no' => $i,
                ]);
            }
        }

        $rack->shelves()->where('shelf_no', '>', $count)->delete();
    }
}
