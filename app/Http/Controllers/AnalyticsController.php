<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductionOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index()
    {
        $now = now();

        $revenue_this_month = Payment::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('amount');
        $revenue_last_month = Payment::whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->sum('amount');

        $orders_active = ProductionOrder::whereNotIn('stage', ['completed'])->count();
        $orders_completed = ProductionOrder::where('stage', 'completed')->whereYear('updated_at', $now->year)->whereMonth('updated_at', $now->month)->count();

        $outstanding = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('balance_due');
        $active_clients = Client::where('status', 'active')->count();

        // 6-month trend
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $months[] = [
                'label' => $m->format('M Y'),
                'revenue' => Payment::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('amount'),
                'expenses' => Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount'),
                'orders' => ProductionOrder::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
            ];
        }

        // Top 5 clients by revenue (all time)
        $top_clients = Invoice::whereIn('status', ['paid', 'partial'])
            ->select('client_id', DB::raw('SUM(total) as total_revenue'))
            ->groupBy('client_id')->orderByDesc('total_revenue')
            ->with('client')->limit(5)->get();

        return view('analytics.index', compact('revenue_this_month', 'revenue_last_month', 'orders_active', 'orders_completed', 'outstanding', 'active_clients', 'months', 'top_clients'));
    }

    public function revenue()
    {
        $now = now();
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $months[] = [
                'label' => $m->format('M Y'),
                'invoiced' => Invoice::whereYear('issue_date', $m->year)->whereMonth('issue_date', $m->month)->sum('total'),
                'collected' => Payment::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('amount'),
                'expenses' => Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount'),
            ];
        }

        $by_vertical = Invoice::whereIn('status', ['paid', 'partial'])->whereYear('issue_date', $now->year)
            ->select('vertical', DB::raw('SUM(total) as total'))
            ->groupBy('vertical')->orderByDesc('total')->get();

        return view('analytics.revenue', compact('months', 'by_vertical'));
    }

    public function productionMetrics()
    {
        $now = now();
        $by_stage = ProductionOrder::select('stage', DB::raw('count(*) as count'))->groupBy('stage')->pluck('count', 'stage');
        $by_priority = ProductionOrder::select('priority', DB::raw('count(*) as count'))->groupBy('priority')->pluck('count', 'priority');

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $monthly[] = [
                'label' => $m->format('M Y'),
                'created' => ProductionOrder::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
                'completed' => ProductionOrder::where('stage', 'completed')->whereYear('updated_at', $m->year)->whereMonth('updated_at', $m->month)->count(),
            ];
        }

        return view('analytics.production', compact('by_stage', 'by_priority', 'monthly'));
    }

    public function clientMetrics()
    {
        $by_type = Client::select('type', DB::raw('count(*) as count'))->groupBy('type')->pluck('count', 'type');
        $by_status = Client::select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status');

        $top_clients = Invoice::select('client_id', DB::raw('SUM(total) as total_revenue, COUNT(*) as invoice_count'))
            ->whereIn('status', ['paid', 'partial'])
            ->groupBy('client_id')->orderByDesc('total_revenue')
            ->with('client')->limit(10)->get();

        return view('analytics.clients', compact('by_type', 'by_status', 'top_clients'));
    }

    public function staffPerformance()
    {
        $employees = Employee::where('status', 'active')->with('department')->withCount(['attendance' => fn ($q) => $q->whereYear('date', now()->year)->whereMonth('date', now()->month)])->get();

        return view('analytics.staff', compact('employees'));
    }

    public function inventoryTrends()
    {
        $low_stock = InventoryItem::whereIn('status', ['low_stock', 'out_of_stock'])->count();
        $total_value = InventoryItem::selectRaw('SUM(quantity * unit_cost) as val')->value('val') ?? 0;
        $by_status = InventoryItem::select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status');

        return view('analytics.inventory', compact('low_stock', 'total_value', 'by_status'));
    }

    public function export(Request $request, string $report)
    {
        if (! in_array($report, ['revenue', 'production', 'clients', 'staff', 'inventory'], true)) {
            abort(404);
        }

        $data = $this->exportData($report);
        $format = strtolower((string) $request->query('format', 'csv'));
        $file = $report.'-'.now()->format('Y-m-d').'.'.($format === 'json' ? 'json' : 'csv');
        $headers = [
            'Content-Disposition' => 'attachment; filename="'.$file.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($format === 'json') {
            return response()->json([
                'report' => $report,
                'generated_at' => now()->toIso8601String(),
                'summary' => $data['summary'],
                'columns' => $data['headers'],
                'data' => $data['rows'],
            ], 200, $headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $data['headers']);
            foreach ($data['rows'] as $row) {
                $line = [];
                foreach ($data['headers'] as $column) {
                    $value = $row[$column] ?? '';
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $line[] = $value === null ? '' : (string) $value;
                }
                fputcsv($handle, $line);
            }
            fclose($handle);
        }, 200, $headers + ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exportData(string $report): array
    {
        return match ($report) {
            'revenue' => $this->revenueExport(),
            'production' => $this->productionExport(),
            'clients' => $this->clientsExport(),
            'staff' => $this->staffExport(),
            'inventory' => $this->inventoryExport(),
        };
    }

    private function revenueExport(): array
    {
        $now = now();
        $rows = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $invoiced = (float) Invoice::whereYear('issue_date', $m->year)->whereMonth('issue_date', $m->month)->sum('total');
            $collected = (float) Payment::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('amount');
            $expenses = (float) Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount');
            $rows[] = [
                'Month' => $m->format('M Y'),
                'Invoiced' => $invoiced,
                'Collected' => $collected,
                'Expenses' => $expenses,
                'Net' => $collected - $expenses,
            ];
        }

        $byVertical = Invoice::whereIn('status', ['paid', 'partial'])->whereYear('issue_date', $now->year)
            ->select('vertical', DB::raw('SUM(total) as total'))
            ->groupBy('vertical')->orderByDesc('total')->get()
            ->map(fn ($r) => ['vertical' => $r->vertical ?? 'unassigned', 'total' => (float) $r->total])
            ->all();

        return [
            'headers' => ['Month', 'Invoiced', 'Collected', 'Expenses', 'Net'],
            'rows' => $rows,
            'summary' => ['period' => 'Last 12 months', 'by_vertical' => $byVertical],
        ];
    }

    private function productionExport(): array
    {
        $now = now();
        $byStage = ProductionOrder::select('stage', DB::raw('count(*) as count'))->groupBy('stage')->pluck('count', 'stage')->all();
        $byPriority = ProductionOrder::select('priority', DB::raw('count(*) as count'))->groupBy('priority')->pluck('count', 'priority')->all();
        $rows = [];

        foreach (['brief', 'design', 'production', 'delivery', 'completed', 'cancelled'] as $stage) {
            $rows[] = ['Section' => 'By Stage', 'Label' => ucfirst($stage), 'Period' => 'All time', 'Value' => (int) ($byStage[$stage] ?? 0)];
        }
        foreach (['urgent', 'high', 'normal', 'low'] as $priority) {
            $rows[] = ['Section' => 'By Priority', 'Label' => ucfirst($priority), 'Period' => 'All time', 'Value' => (int) ($byPriority[$priority] ?? 0)];
        }

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $created = ProductionOrder::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count();
            $completed = ProductionOrder::where('stage', 'completed')->whereYear('updated_at', $m->year)->whereMonth('updated_at', $m->month)->count();
            $monthly[] = ['label' => $m->format('M Y'), 'created' => $created, 'completed' => $completed];
            $rows[] = ['Section' => 'Monthly Volume', 'Label' => 'Created', 'Period' => $m->format('M Y'), 'Value' => $created];
            $rows[] = ['Section' => 'Monthly Volume', 'Label' => 'Completed', 'Period' => $m->format('M Y'), 'Value' => $completed];
        }

        return [
            'headers' => ['Section', 'Label', 'Period', 'Value'],
            'rows' => $rows,
            'summary' => ['by_stage' => $byStage, 'by_priority' => $byPriority, 'monthly' => $monthly],
        ];
    }

    private function clientsExport(): array
    {
        $byType = Client::select('type', DB::raw('count(*) as count'))->groupBy('type')->pluck('count', 'type')->all();
        $byStatus = Client::select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->all();
        $top = Invoice::select('client_id', DB::raw('SUM(total) as total_revenue, COUNT(*) as invoice_count'))
            ->whereIn('status', ['paid', 'partial'])
            ->groupBy('client_id')->orderByDesc('total_revenue')
            ->with('client')->limit(10)->get();

        $rows = [];
        foreach ($byType as $type => $count) {
            $rows[] = ['Section' => 'By Type', 'Label' => ucfirst((string) $type), 'Count' => (int) $count, 'Revenue' => ''];
        }
        foreach ($byStatus as $status => $count) {
            $rows[] = ['Section' => 'By Status', 'Label' => ucfirst((string) $status), 'Count' => (int) $count, 'Revenue' => ''];
        }
        foreach ($top as $tc) {
            $rows[] = [
                'Section' => 'Top Clients',
                'Label' => $tc->client?->name ?? 'Unknown',
                'Count' => (int) $tc->invoice_count,
                'Revenue' => (float) $tc->total_revenue,
            ];
        }

        return [
            'headers' => ['Section', 'Label', 'Count', 'Revenue'],
            'rows' => $rows,
            'summary' => ['by_type' => $byType, 'by_status' => $byStatus],
        ];
    }

    private function staffExport(): array
    {
        $month = now();
        $employees = Employee::where('status', 'active')
            ->withCount(['attendance' => fn ($q) => $q->whereYear('date', $month->year)->whereMonth('date', $month->month)])
            ->orderBy('first_name')->get();

        $rows = $employees->map(fn ($e) => [
            'Employee' => $e->first_name.' '.$e->last_name,
            'Job Title' => $e->job_title,
            'Department' => $e->department?->name ?? '',
            'Attendance Days' => (int) $e->attendance_count,
            'Month' => $month->format('M Y'),
        ])->all();

        return [
            'headers' => ['Employee', 'Job Title', 'Department', 'Attendance Days', 'Month'],
            'rows' => $rows,
            'summary' => ['month' => $month->format('M Y'), 'active_employees' => count($rows)],
        ];
    }

    private function inventoryExport(): array
    {
        $items = InventoryItem::with('category')->orderBy('name')->get();
        $rows = $items->map(fn ($i) => [
            'SKU' => $i->sku,
            'Item' => $i->name,
            'Category' => $i->category?->name ?? '',
            'Unit' => $i->unit,
            'Quantity' => (float) $i->quantity,
            'Unit Cost' => (float) $i->unit_cost,
            'Stock Value' => (float) $i->quantity * (float) $i->unit_cost,
            'Status' => $i->status,
        ])->all();

        return [
            'headers' => ['SKU', 'Item', 'Category', 'Unit', 'Quantity', 'Unit Cost', 'Stock Value', 'Status'],
            'rows' => $rows,
            'summary' => [
                'low_stock' => InventoryItem::whereIn('status', ['low_stock', 'out_of_stock'])->count(),
                'total_value' => (float) (InventoryItem::selectRaw('SUM(quantity * unit_cost) as val')->value('val') ?? 0),
                'by_status' => InventoryItem::select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->all(),
            ],
        ];
    }

    public function revenueChartData()
    {
        $now = now();
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $data[] = [
                'month' => $m->format('M Y'),
                'revenue' => (float) Payment::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('amount'),
            ];
        }

        return response()->json($data);
    }

    public function ordersChartData()
    {
        $now = now();
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $data[] = [
                'month' => $m->format('M Y'),
                'orders' => ProductionOrder::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count(),
            ];
        }

        return response()->json($data);
    }

    public function topClientsData()
    {
        $data = Invoice::select('client_id', DB::raw('SUM(total) as total_revenue'))
            ->whereIn('status', ['paid', 'partial'])->groupBy('client_id')
            ->orderByDesc('total_revenue')->with('client')->limit(5)->get()
            ->map(fn ($r) => ['name' => $r->client?->name ?? 'Unknown', 'revenue' => (float) $r->total_revenue]);

        return response()->json($data);
    }
}
