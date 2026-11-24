<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Services\QuoteBuilderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuoteBuilderController extends Controller
{
    public function __construct(private QuoteBuilderService $builder) {}

    public function create()
    {
        return $this->render();
    }

    public function edit(Invoice $invoice)
    {
        abort_unless($invoice->type === 'quote', 404);
        $invoice->load(['client', 'items' => fn ($q) => $q->orderBy('sort_order')]);

        return $this->render($invoice);
    }

    public function store(Request $request)
    {
        $invoice = DB::transaction(fn () => $this->persist($request));

        return redirect()->route('invoicing.show', $invoice)->with('success', 'Quote saved successfully.');
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->type === 'quote', 404);
        $saved = DB::transaction(fn () => $this->persist($request, $invoice));

        return redirect()->route('invoicing.show', $saved)->with('success', 'Quote updated successfully.');
    }

    private function render(?Invoice $invoice = null)
    {
        $settings = $this->builder->settings();
        $products = $this->builder->quoteProducts();
        $clients = Client::orderBy('name')->get(['id', 'name', 'company', 'email', 'phone']);
        $config = [
            'products' => $products,
            'clients' => $clients,
            'settings' => $settings,
            'invoice' => $invoice ? $this->invoicePayload($invoice) : null,
            'routes' => [
                'store' => route('invoicing.quote-builder.store'),
                'update' => $invoice ? route('invoicing.quote-builder.update', $invoice) : null,
                'show' => route('invoicing.index'),
            ],
        ];

        return view('invoicing.quote-builder', compact('config', 'invoice'));
    }

    private function persist(Request $request, ?Invoice $invoice = null): Invoice
    {
        $payload = $request->all();
        if (is_string($payload['payload'] ?? null)) {
            $decoded = json_decode($payload['payload'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $payload = array_merge($payload, $decoded);
            }
        }
        $data = validator($payload, [
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'valid_days' => 'nullable|integer|min:0|max:365',
            'due_date' => 'nullable|date',
            'currency' => 'nullable|string|max:10',
            'vertical' => 'nullable|in:bags,furniture,interiors,mixed',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'lead_time' => 'nullable|string|max:255',
            'prepared_by' => 'nullable|string|max:255',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.channel' => 'required|in:retail,partner,institutional,employee,wholesale',
            'items.*.branding' => 'required|in:none,screen,embroid,full,leather_patch',
            'items.*.payment_days' => 'nullable|integer|min:0|max:365',
            'items.*.rush' => 'nullable|boolean',
            'items.*.manual_discount_percent' => 'nullable|numeric|min:0|max:80',
            'items.*.strategic_discount_percent' => 'nullable|numeric|min:0|max:80',
        ])->validate();

        $settings = $this->builder->settings();
        try {
            $built = $this->builder->buildQuote($data, $settings);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['items' => $e->getMessage()]);
        }

        $validDays = (int) ($data['valid_days'] ?? $settings['quote_default_valid_days']);
        $issue = Carbon::parse($data['issue_date']);
        $due = ! empty($data['due_date']) ? Carbon::parse($data['due_date']) : $issue->copy()->addDays($validDays);

        $payload = [
            'client_id' => $data['client_id'],
            'type' => 'quote',
            'status' => $invoice?->status ?? 'draft',
            'vertical' => $data['vertical'] ?? null,
            'issue_date' => $issue->toDateString(),
            'due_date' => $due->toDateString(),
            'subtotal' => $built['subtotal'],
            'discount_percent' => $built['discount_percent'],
            'discount_amount' => $built['discount_amount'],
            'tax_percent' => $built['tax_percent'],
            'tax_amount' => $built['tax_amount'],
            'total' => $built['total'],
            'amount_paid' => $invoice?->amount_paid ?? 0,
            'balance_due' => round($built['total'] - ($invoice?->amount_paid ?? 0), 2),
            'currency' => ($data['currency'] ?? null) ?: 'ZMW',
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? ($settings['quote_default_terms'] ?? null),
            'meta' => array_merge($built['meta'], ['valid_days' => $validDays]),
        ];

        if ($invoice) {
            $invoice->update($payload);
        } else {
            $payload['invoice_number'] = $this->nextQuoteNumber();
            $payload['created_by'] = Auth::id();
            $invoice = Invoice::create($payload);
        }

        $invoice->items()->delete();
        foreach (array_values($built['lines']) as $index => $line) {
            $invoice->items()->create($line + ['sort_order' => $index]);
        }

        return $invoice->fresh(['items']);
    }

    private function nextQuoteNumber(): string
    {
        $last = Invoice::withTrashed()->where('invoice_number', 'like', 'MPS-QUO-%')->orderByDesc('id')->value('invoice_number');
        $seq = $last ? ((int) preg_replace('/\D/', '', substr($last, -4)) + 1) : 1;

        return 'MPS-QUO-'.date('Y').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function invoicePayload(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client_id' => $invoice->client_id,
            'issue_date' => optional($invoice->issue_date)->format('Y-m-d'),
            'due_date' => optional($invoice->due_date)->format('Y-m-d'),
            'valid_days' => data_get($invoice->meta, 'valid_days', 30),
            'currency' => $invoice->currency,
            'vertical' => $invoice->vertical,
            'notes' => $invoice->notes,
            'terms' => $invoice->terms,
            'lead_time' => data_get($invoice->meta, 'lead_time'),
            'prepared_by' => data_get($invoice->meta, 'prepared_by'),
            'discount_percent' => $invoice->discount_percent,
            'tax_percent' => $invoice->tax_percent,
            'items' => $invoice->items->map(fn ($i) => [
                'product_id' => data_get($i->meta, 'product_id'),
                'quantity' => $i->quantity,
                'channel' => data_get($i->meta, 'channel', 'retail'),
                'branding' => data_get($i->meta, 'branding', 'none'),
                'payment_days' => data_get($i->meta, 'payment_days', 0),
                'rush' => (bool) data_get($i->meta, 'rush', false),
                'manual_discount_percent' => data_get($i->meta, 'manual_discount_percent', 0),
                'strategic_discount_percent' => data_get($i->meta, 'strategic_discount_percent', 0),
            ])->values(),
        ];
    }
}
