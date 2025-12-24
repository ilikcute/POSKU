<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchasePlan;
use App\Models\PurchasePlanItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchasePlanController extends Controller
{
    public function index()
    {
        $plans = PurchasePlan::with(['supplier', 'creator'])
            ->where('store_id', $this->currentStoreId())
            ->orderBy('plan_date', 'desc')
            ->orderBy('doc_no', 'desc')
            ->paginate(15);

        return Inertia::render('Purchases/Plans/Index', [
            'plans' => $plans,
        ]);
    }

    public function show(PurchasePlan $plan)
    {
        if ($plan->store_id !== $this->currentStoreId()) {
            abort(403, 'Rencana pembelian ini bukan milik toko Anda.');
        }

        $plan->load(['supplier', 'creator', 'items.product']);

        return Inertia::render('Purchases/Plans/Show', [
            'plan' => $plan,
        ]);
    }

    public function items(PurchasePlan $plan)
    {
        if ($plan->store_id !== $this->currentStoreId()) {
            abort(403, 'Rencana pembelian ini bukan milik toko Anda.');
        }

        $plan->load(['supplier', 'items.product']);

        return response()->json([
            'id' => $plan->id,
            'doc_no' => $plan->doc_no,
            'supplier' => $plan->supplier,
            'items' => $plan->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'planned_qty' => $item->planned_qty,
                    'product' => [
                        'id' => $item->product?->id,
                        'product_code' => $item->product?->product_code,
                        'name' => $item->product?->name,
                        'unit' => $item->product?->unit,
                        'purchase_price' => $item->product?->purchase_price,
                        'tax_type' => $item->product?->tax_type,
                        'tax_rate' => $item->product?->tax_rate,
                        'supplier_id' => $item->product?->supplier_id,
                    ],
                ];
            }),
        ]);
    }

    public function print(PurchasePlan $plan)
    {
        if ($plan->store_id !== $this->currentStoreId()) {
            abort(403, 'Rencana pembelian ini bukan milik toko Anda.');
        }

        $plan->load(['supplier', 'creator', 'items.product', 'store']);

        return view('purchases.plan-print', [
            'plan' => $plan,
        ]);
    }

    public function generatePDF(PurchasePlan $plan)
    {
        if ($plan->store_id !== $this->currentStoreId()) {
            abort(403, 'Rencana pembelian ini bukan milik toko Anda.');
        }

        $plan->load(['supplier', 'creator', 'items.product', 'store']);

        $pdf = Pdf::loadView('purchases.plan-print', [
            'plan' => $plan,
        ]);

        return $pdf->download('rencana-pembelian-' . $plan->doc_no . '.pdf');
    }

    public function generate(Request $request)
    {
        $storeId = $this->currentStoreId();
        $today = now()->toDateString();

        $products = Product::query()
            ->select('products.*', DB::raw('COALESCE(stocks.quantity, 0) as current_stock'))
            ->leftJoin('stocks', function ($join) use ($storeId) {
                $join->on('stocks.product_id', '=', 'products.id')
                    ->where('stocks.store_id', '=', $storeId)
                    ->whereNull('stocks.deleted_at');
            })
            ->whereNotNull('products.supplier_id')
            ->where('products.max_stock_alert', '>', 0)
            ->get();

        $grouped = [];
        foreach ($products as $product) {
            $currentStock = (int) $product->current_stock;
            $maxStock = (int) $product->max_stock_alert;
            $needed = max(0, $maxStock - $currentStock);

            if ($needed <= 0) {
                continue;
            }

            $minOrder = max(1, (int) ($product->min_order_qty ?? 1));
            $planned = (int) (floor($needed / $minOrder) * $minOrder);

            if ($planned <= 0) {
                continue;
            }

            $grouped[$product->supplier_id][] = [
                'product_id' => $product->id,
                'current_stock' => $currentStock,
                'max_stock' => $maxStock,
                'min_order_qty' => $minOrder,
                'needed_qty' => $needed,
                'planned_qty' => $planned,
            ];
        }

        if (! $grouped) {
            return back()->with('error', 'Tidak ada item yang perlu direncanakan.');
        }

        DB::transaction(function () use ($grouped, $storeId, $today) {
            $nextDocNo = (int) PurchasePlan::where('store_id', $storeId)->max('doc_no');

            foreach ($grouped as $supplierId => $items) {
                $nextDocNo++;
                $totalItems = count($items);
                $totalQty = collect($items)->sum('planned_qty');

                $plan = PurchasePlan::create([
                    'store_id' => $storeId,
                    'supplier_id' => $supplierId,
                    'created_by' => Auth::id(),
                    'doc_no' => $nextDocNo,
                    'plan_date' => $today,
                    'total_items' => $totalItems,
                    'total_qty' => $totalQty,
                ]);

                $now = now();
                $itemsPayload = [];
                foreach ($items as $item) {
                    $itemsPayload[] = array_merge($item, [
                        'purchase_plan_id' => $plan->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                PurchasePlanItem::insert($itemsPayload);
            }
        });

        return redirect()->route('purchases.plans.index')
            ->with('success', 'Rencana pembelian berhasil dibuat.');
    }
}
