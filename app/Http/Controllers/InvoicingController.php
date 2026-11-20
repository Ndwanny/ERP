<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoicingController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('client');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('invoice_number', 'like', "%{$q}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }
        $invoices = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'total' => Invoice::count(),
            'draft' => Invoice::where('status', 'draft')->count(),
            'sent' => Invoice::whereIn('status', ['sent', 'viewed'])->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'outstanding' => Invoice::whereIn('status', ['sent', 'viewed', 'partial', 'overdue'])->sum('balance_due'),
        ];

        return view('invoicing.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'company']);
        $type = 'invoice';

        return view('invoicing.create', compact('clients', 'type'));
    }

    public function createQuote()
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'company']);
        $type = 'quote';

        return view('invoicing.create', compact('clients', 'type'));
    }

    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);
        $totals = $this->calculateTotals($data['items'], (float) ($data['discount_percent'] ?? 0), (float) ($data['tax_percent'] ?? 16));
        $data['invoice_number'] = $this->generateNumber($data['type']);
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';
        $data['due_date'] = $data['due_date'] ?? $data['issue_date'];
        $data['currency'] = ($data['currency'] ?? null) ?: 'ZMW';
        $data['subtotal'] = $totals['subtotal'];
        $data['discount_amount'] = $totals['discount_amount'];
        $data['tax_amount'] = $totals['tax_amount'];
        $data['total'] = $totals['total'];
        $data['balance_due'] = $totals['total'];
        $items = $data['items'];
        unset($data['items']);

        $invoice = Invoice::create($data);
        $this->syncItems($invoice, $items);

        return redirect()->route('invoicing.show', $invoice)->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'items' => fn ($q) => $q->orderBy('sort_order'), 'payments' => fn ($q) => $q->latest()]);

        return view('invoicing.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'company']);
        $invoice->load(['items' => fn ($q) => $q->orderBy('sort_order')]);
        $type = $invoice->type;

        return view('invoicing.edit', compact('invoice', 'clients', 'type'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateInvoice($request);
        $totals = $this->calculateTotals($data['items'], (float) ($data['discount_percent'] ?? 0), (float) ($data['tax_percent'] ?? 16));
        $data['due_date'] = $data['due_date'] ?? $data['issue_date'];
        $data['currency'] = ($data['currency'] ?? null) ?: 'ZMW';
        $data['subtotal'] = $totals['subtotal'];
        $data['discount_amount'] = $totals['discount_amount'];
        $data['tax_amount'] = $totals['tax_amount'];
        $data['total'] = $totals['total'];
        $data['balance_due'] = round($totals['total'] - $invoice->amount_paid, 2);
        $items = $data['items'];
        unset($data['items']);

        $invoice->update($data);
        $this->syncItems($invoice, $items);

        return redirect()->route('invoicing.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('invoicing.index')->with('success', 'Invoice deleted.');
    }

    public function send(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be sent again.');
        }
        $invoice->update(['status' => 'sent', 'sent_at' => now()]);

        return back()->with('success', 'Invoice marked as sent.');
    }

    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['client', 'items' => fn ($q) => $q->orderBy('sort_order')]);
        $html = view('invoicing.pdf', compact('invoice'))->render();
        $disposition = request()->boolean('download') ? 'attachment' : 'inline';

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', $disposition.'; filename="'.$invoice->invoice_number.'.html"');
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'method' => 'required|in:cash,bank_transfer,mobile_money,cheque,card',
            'reference' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $amount = round((float) ($data['amount'] ?? $invoice->balance_due), 2);
        if ($amount <= 0 || $amount > $invoice->balance_due + 0.01) {
            return back()->with('error', 'Payment amount must be greater than zero and not exceed the balance due.');
        }

        $amountPaid = round($invoice->amount_paid + $amount, 2);
        $fullyPaid = $amountPaid >= $invoice->total - 0.01;

        Payment::create([
            'invoice_id' => $invoice->id,
            'recorded_by' => Auth::id(),
            'amount' => $amount,
            'currency' => $invoice->currency,
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'payment_date' => $data['payment_date'] ?? today(),
            'notes' => $data['notes'] ?? null,
        ]);

        $invoice->update([
            'amount_paid' => $amountPaid,
            'balance_due' => round($invoice->total - $amountPaid, 2),
            'paid_at' => $fullyPaid ? now() : $invoice->paid_at,
            'status' => $fullyPaid ? 'paid' : 'partial',
        ]);

        $this->postPaymentJournal($invoice, $amount, $data['method']);

        return back()->with('success', $fullyPaid ? 'Invoice fully paid.' : 'Partial payment recorded.');
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $invoices = Invoice::where('invoice_number', 'like', "%{$q}%")
            ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$q}%"))
            ->limit(10)
            ->get(['id', 'invoice_number', 'client_id', 'total', 'status']);

        return response()->json($invoices);
    }

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:quote,proforma,invoice,receipt,credit_note',
            'vertical' => 'nullable|in:bags,furniture,interiors,mixed',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    private function calculateTotals(array $items, float $discountPercent, float $taxPercent): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float) $item['quantity'] * (float) $item['unit_price'];
        }
        $subtotal = round($subtotal, 2);
        $discount = round($subtotal * $discountPercent / 100, 2);
        $tax = round(($subtotal - $discount) * $taxPercent / 100, 2);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total' => round($subtotal - $discount + $tax, 2),
        ];
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        foreach (array_values($items) as $index => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?: 'pcs',
                'unit_price' => $item['unit_price'],
                'total' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
                'sort_order' => $index,
            ]);
        }
    }

    private function generateNumber(string $type): string
    {
        $prefix = $type === 'quote' ? 'MPS-QUO-' : 'MPS-INV-';
        $last = Invoice::withTrashed()->orderByDesc('id')->value('invoice_number');
        $seq = $last ? (int) preg_replace('/\D/', '', $last) + 1 : 1;

        return $prefix.date('Y').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function postPaymentJournal(Invoice $invoice, float $amount, string $method): void
    {
        try {
            $cashCode = $method === 'cash' ? '1000' : ($method === 'mobile_money' ? '1020' : '1010');
            $cash = ChartOfAccount::where('code', $cashCode)->first();
            $receivable = ChartOfAccount::where('code', '1100')->first();
            if (! $cash || ! $receivable) {
                return;
            }
            $reference = 'JNL-'.date('Y').'-'.str_pad((string) ((JournalEntry::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
            $entry = JournalEntry::create([
                'reference' => $reference,
                'user_id' => Auth::id(),
                'entry_date' => today(),
                'description' => 'Payment received for '.$invoice->invoice_number,
                'source' => 'payment',
                'source_id' => $invoice->id,
            ]);
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $cash->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Payment received',
            ]);
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $receivable->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Settlement of '.$invoice->invoice_number,
            ]);
        } catch (\Throwable $e) {
            return;
        }
    }
}
