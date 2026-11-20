<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function index()
    {
        $now = now();
        $month_start = $now->copy()->startOfMonth();

        $income_this_month = Payment::whereBetween('created_at', [$month_start, $now])->sum('amount');
        $expenses_this_month = Expense::whereBetween('expense_date', [$month_start, $now])->sum('amount');
        $outstanding = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('balance_due');
        $expense_count = Expense::whereMonth('expense_date', $now->month)->count();

        // Monthly trend: last 6 months
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $months[] = [
                'label' => $m->format('M Y'),
                'income' => Payment::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('amount'),
                'expenses' => Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount'),
            ];
        }

        return view('accounting.index', compact('income_this_month', 'expenses_this_month', 'outstanding', 'expense_count', 'months'));
    }

    public function expenses()
    {
        $expenses = Expense::with(['supplier', 'recordedBy'])->latest()->paginate(25);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company']);
        $categories = ['materials', 'utilities', 'rent', 'transport', 'marketing', 'salaries', 'equipment', 'maintenance', 'other'];

        return view('accounting.expenses', compact('expenses', 'suppliers', 'categories'));
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'category' => 'required|in:materials,utilities,rent,transport,marketing,salaries,equipment,maintenance,other',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'vertical' => 'nullable|in:bags,furniture,interiors,admin,shared',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,card',
            'description' => 'nullable|string',
        ]);

        $data['recorded_by'] = Auth::id();
        $data['currency'] = 'ZMW';
        Expense::create($data);

        return redirect()->route('accounting.expenses')->with('success', 'Expense recorded.');
    }

    public function income()
    {
        $payments = Payment::with('invoice.client')->latest()->paginate(25);
        $total_month = Payment::whereMonth('created_at', now()->month)->sum('amount');

        return view('accounting.income', compact('payments', 'total_month'));
    }

    public function suppliers()
    {
        $suppliers = Supplier::withCount('expenses')->orderBy('name')->paginate(20);

        return view('accounting.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'tpin' => 'nullable|string|max:50',
            'category' => 'required|in:fabric,hardware,timber,packaging,equipment,services,other',
        ]);

        $data['is_active'] = true;
        Supplier::create($data);

        return redirect()->route('accounting.suppliers')->with('success', 'Supplier added.');
    }

    public function ledger()
    {
        $now = now();
        $month = $now->format('Y-m');

        // Combine income (payments) and expenses into a unified ledger view
        $income = Payment::with('invoice.client')
            ->selectRaw("'income' as entry_type, amount, method as payment_method, created_at, invoice_id as ref_id, notes, NULL as category")
            ->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)
            ->get();

        $expenses = Expense::with('supplier')
            ->selectRaw("'expense' as entry_type, amount, payment_method, expense_date as created_at, id as ref_id, description as notes, category")
            ->whereYear('expense_date', $now->year)->whereMonth('expense_date', $now->month)
            ->get();

        $entries = $income->concat($expenses)->sortByDesc('created_at');
        $total_income = $income->sum('amount');
        $total_expenses = $expenses->sum('amount');
        $net = $total_income - $total_expenses;

        return view('accounting.ledger', compact('entries', 'total_income', 'total_expenses', 'net', 'month'));
    }

    public function profitLoss()
    {
        $now = now();
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $inc = Payment::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->sum('amount');
            $exp = Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount');
            $months[] = ['label' => $m->format('M Y'), 'income' => $inc, 'expenses' => $exp, 'profit' => $inc - $exp];
        }

        // Expense breakdown by category for current year
        $by_category = Expense::whereYear('expense_date', $now->year)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')->orderByDesc('total')->get();

        return view('accounting.profit-loss', compact('months', 'by_category'));
    }

    public function zraReport()
    {
        $now = now();
        // VAT-eligible income: invoices with tax
        $vat_collected = Invoice::whereIn('status', ['paid', 'partial'])
            ->whereYear('issue_date', $now->year)
            ->whereMonth('issue_date', $now->month)
            ->sum('tax_amount');

        $total_sales = Invoice::whereIn('status', ['paid', 'partial'])
            ->whereYear('issue_date', $now->year)
            ->whereMonth('issue_date', $now->month)
            ->sum('total');

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $months[] = [
                'label' => $m->format('M Y'),
                'total_sales' => Invoice::whereIn('status', ['paid', 'partial'])->whereYear('issue_date', $m->year)->whereMonth('issue_date', $m->month)->sum('total'),
                'vat_collected' => Invoice::whereIn('status', ['paid', 'partial'])->whereYear('issue_date', $m->year)->whereMonth('issue_date', $m->month)->sum('tax_amount'),
            ];
        }

        return view('accounting.zra-report', compact('vat_collected', 'total_sales', 'months'));
    }

    public function monthlyReport()
    {
        return redirect()->route('accounting.reports.profit-loss');
    }

    public function bankReconciliation()
    {
        $payments = Payment::with('invoice.client')->latest()->limit(50)->get();
        $total = $payments->sum('amount');

        return view('accounting.bank-reconciliation', compact('payments','total'));
    }
}
