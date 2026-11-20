<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with('category')->latest()->paginate(20);
        $stats = [
            'total' => InventoryItem::count(),
            'in_stock' => InventoryItem::where('status', 'in_stock')->count(),
            'low_stock' => InventoryItem::where('status', 'low_stock')->count(),
            'out_of_stock' => InventoryItem::where('status', 'out_of_stock')->count(),
        ];

        return view('inventory.index', compact('items', 'stats'));
    }

    public function create()
    {
        $categories = InventoryCategory::orderBy('name')->get();

        return view('inventory.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);
        $data['status'] = $this->statusFor((float) $data['quantity'], (float) ($data['min_quantity'] ?? 0));
        $item = InventoryItem::create($data);

        if ($item->quantity > 0) {
            InventoryTransaction::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'type' => 'restock',
                'quantity' => $item->quantity,
                'quantity_before' => 0,
                'quantity_after' => $item->quantity,
                'reference' => 'Initial stock',
                'notes' => null,
            ]);
        }

        return redirect()->route('inventory.show', $item)->with('success', 'Inventory item added.');
    }

    public function show(InventoryItem $item)
    {
        $item->load(['category', 'transactions' => fn ($q) => $q->with('user')->latest()]);
        $categories = InventoryCategory::orderBy('name')->get();

        return view('inventory.show', compact('item', 'categories'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $data = $this->validateItem($request, $item);
        $before = (float) $item->quantity;
        $data['status'] = $this->statusFor((float) $data['quantity'], (float) ($data['min_quantity'] ?? 0));
        $item->update($data);
        $after = (float) $item->quantity;

        if (abs($after - $before) > 0.001) {
            InventoryTransaction::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'type' => 'adjustment',
                'quantity' => abs($after - $before),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference' => $request->reference,
                'notes' => 'Stock level adjusted',
            ]);
        }

        return redirect()->route('inventory.show', $item)->with('success', 'Inventory item updated.');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted.');
    }

    public function restock(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $this->applyTransaction($item, 'restock', (float) $data['quantity'], $data);

        return back()->with('success', 'Stock restocked successfully.');
    }

    public function deduct(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.01|lte:'.$item->quantity,
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $this->applyTransaction($item, 'deduction', -(float) $data['quantity'], $data);

        return back()->with('success', 'Stock deducted successfully.');
    }

    public function lowStockAlerts()
    {
        $items = InventoryItem::with('category')
            ->whereIn('status', ['low_stock', 'out_of_stock'])
            ->orderBy('status')
            ->orderBy('name')
            ->paginate(20);
        $stats = [
            'low_stock' => InventoryItem::where('status', 'low_stock')->count(),
            'out_of_stock' => InventoryItem::where('status', 'out_of_stock')->count(),
        ];

        return view('inventory.alerts', compact('items', 'stats'));
    }

    public function usageReport(Request $request)
    {
        $query = InventoryTransaction::with('item', 'user');
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        $transactions = $query->latest()->paginate(20)->withQueryString();
        $items = InventoryItem::orderBy('name')->get(['id', 'name', 'sku']);
        $totals = [
            'transactions' => InventoryTransaction::count(),
            'restocked' => InventoryTransaction::where('type', 'restock')->sum('quantity'),
            'deducted' => InventoryTransaction::where('type', 'deduction')->sum('quantity'),
            'damaged' => InventoryTransaction::where('type', 'damage')->sum('quantity'),
        ];

        return view('inventory.usage-report', compact('transactions', 'items', 'totals'));
    }

    private function validateItem(Request $request, ?InventoryItem $item = null): array
    {
        return $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name' => 'required|string|max:150',
            'sku' => 'required|string|max:50|unique:inventory_items,sku'.($item ? ','.$item->id : ''),
            'description' => 'nullable|string',
            'unit' => 'required|string|max:30',
            'quantity' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:100',
        ]);
    }

    private function statusFor(float $quantity, float $min): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        }
        if ($quantity <= $min) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    private function applyTransaction(InventoryItem $item, string $type, float $delta, array $data): void
    {
        $before = (float) $item->quantity;
        $after = round(max(0, $before + $delta), 2);
        $item->update([
            'quantity' => $after,
            'status' => $this->statusFor($after, (float) $item->min_quantity),
        ]);
        InventoryTransaction::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => abs($delta),
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
