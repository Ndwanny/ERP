<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\ProductionOrder;
use App\Models\ProductionStageLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionOrder::with('client', 'assignedTo');
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        $orders = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'total' => ProductionOrder::count(),
            'active' => ProductionOrder::whereIn('stage', ['brief', 'design', 'production', 'delivery'])->count(),
            'production' => ProductionOrder::where('stage', 'production')->count(),
            'completed' => ProductionOrder::where('stage', 'completed')->count(),
            'urgent' => ProductionOrder::where('priority', 'urgent')->where('stage', '!=', 'completed')->count(),
            'overdue' => ProductionOrder::where('stage', '!=', 'completed')->whereDate('due_date', '<', today())->count(),
        ];

        return view('production.index', compact('orders', 'stats'));
    }

    public function kanban()
    {
        $stages = ['brief', 'design', 'production', 'delivery'];
        $orders = ProductionOrder::with('client', 'assignedTo')
            ->whereIn('stage', $stages)
            ->get()
            ->sortBy(fn ($order) => ['urgent' => 0, 'high' => 1, 'normal' => 2, 'low' => 3][$order->priority] ?? 4)
            ->values();

        return view('production.board', compact('orders', 'stages'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'company']);
        $users = User::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $invoices = Invoice::orderByDesc('issue_date')->limit(100)->get(['id', 'invoice_number', 'client_id']);
        $materials = InventoryItem::orderBy('name')->get(['id', 'name', 'sku', 'unit', 'quantity']);

        return view('production.create', compact('clients', 'users', 'invoices', 'materials'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'assigned_to' => 'nullable|exists:users,id',
            'vertical' => 'required|in:bags,furniture,interiors',
            'product_type' => 'required|string|max:100',
            'brief' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'priority' => 'required|in:low,normal,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'quoted_price' => 'nullable|numeric|min:0',
            'internal_notes' => 'nullable|string',
            'measurements' => 'nullable|string',
            'colour_specs' => 'nullable|string',
            'material_item_id' => 'nullable|exists:inventory_items,id',
            'material_quantity' => 'nullable|numeric|min:0',
        ]);

        $data['order_number'] = $this->generateNumber();
        $data['stage'] = 'brief';
        $data['stage_progress'] = 0;
        $data['measurements'] = $this->parseSpecs($data['measurements'] ?? null);
        $data['colour_specs'] = $this->parseSpecs($data['colour_specs'] ?? null);
        $data['fabric_requirements'] = [];

        if (! empty($data['material_item_id'])) {
            $material = InventoryItem::find($data['material_item_id']);
            if ($material) {
                $data['fabric_requirements'] = [[
                    'item_id' => $material->id,
                    'name' => $material->name,
                    'sku' => $material->sku,
                    'quantity' => (float) ($data['material_quantity'] ?? 0),
                    'unit' => $material->unit,
                ]];
            }
        }
        unset($data['material_item_id'], $data['material_quantity']);

        $order = ProductionOrder::create($data);
        ProductionStageLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_stage' => 'new',
            'to_stage' => 'brief',
            'notes' => 'Order created',
            'created_at' => now(),
        ]);

        return redirect()->route('production.show', $order)->with('success', 'Production order created.');
    }

    public function show(ProductionOrder $order)
    {
        $order->load(['client', 'invoice', 'assignedTo', 'stageLogs' => fn ($q) => $q->latest()]);
        $users = User::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('production.show', compact('order', 'users'));
    }

    public function updateStage(Request $request, ProductionOrder $order)
    {
        $data = $request->validate([
            'stage' => 'required|in:brief,design,production,delivery,completed,cancelled',
            'notes' => 'nullable|string',
            'stage_progress' => 'nullable|integer|min:0|max:100',
        ]);

        $from = $order->stage;
        $progress = [
            'brief' => 25,
            'design' => 50,
            'production' => 75,
            'delivery' => 100,
            'completed' => 100,
            'cancelled' => $order->stage_progress,
        ];
        $update = [
            'stage' => $data['stage'],
            'stage_progress' => $data['stage_progress'] ?? $progress[$data['stage']],
        ];
        if ($data['stage'] === 'completed') {
            $update['completed_date'] = today();
        }

        ProductionStageLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_stage' => $from,
            'to_stage' => $data['stage'],
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
        ]);
        $order->update($update);

        return back()->with('success', 'Stage updated to '.ucfirst($data['stage']).'.');
    }

    public function qcCheckpoint(Request $request, ProductionOrder $order)
    {
        $data = $request->validate([
            'qc_passed' => 'required|boolean',
            'qc_notes' => 'nullable|string',
        ]);
        $order->update([
            'qc_passed' => (bool) $data['qc_passed'],
            'qc_notes' => $data['qc_notes'] ?? null,
        ]);

        return back()->with('success', 'QC checkpoint saved.');
    }

    public function assignStaff(Request $request, ProductionOrder $order)
    {
        $data = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'internal_notes' => 'nullable|string',
        ]);
        $update = ['assigned_to' => $data['assigned_to'] ?? null];
        if (! empty($data['priority'])) {
            $update['priority'] = $data['priority'];
        }
        if (array_key_exists('internal_notes', $data)) {
            $update['internal_notes'] = $data['internal_notes'];
        }
        $order->update($update);

        return back()->with('success', 'Staff assignment saved.');
    }

    public function calendar(Request $request)
    {
        $month = (string) $request->get('month', date('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $start = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $cursor = $start->copy()->startOfWeek();
        $end = $start->copy()->endOfMonth()->endOfWeek();
        $orders = ProductionOrder::with('client')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$cursor->copy(), $end->copy()])
            ->get();

        $weeks = [];
        while ($cursor <= $end) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $cursor->copy();
                $week[] = [
                    'date' => $day,
                    'inMonth' => $day->month === $start->month,
                    'orders' => $orders->filter(fn ($o) => $o->due_date->isSameDay($day)),
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }
        $previous = $start->copy()->subMonth()->format('Y-m');
        $next = $start->copy()->addMonth()->format('Y-m');

        return view('production.calendar', compact('weeks', 'start', 'month', 'previous', 'next'));
    }

    private function generateNumber(): string
    {
        $last = ProductionOrder::withTrashed()->orderByDesc('id')->value('order_number');
        $seq = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;

        return 'MPS-ORD-'.date('Y').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function parseSpecs(?string $text): array
    {
        if (! $text) {
            return [];
        }
        $result = [];
        foreach (preg_split('/\R/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $result[trim($key)] = trim($value);
            } else {
                $result[] = $line;
            }
        }

        return $result;
    }
}
