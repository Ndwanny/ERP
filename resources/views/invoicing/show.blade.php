@extends('layouts.app')
@section('title', $invoice->invoice_number)
@section('content')
@php
$badge = ['draft'=>'badge-muted','sent'=>'badge-gold','viewed'=>'badge-navy','partial'=>'badge-gold','paid'=>'badge-success','overdue'=>'badge-error','cancelled'=>'badge-muted'][$invoice->status] ?? 'badge-muted';
@endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">Smart Invoicing</div>
            <h1 class="page-title">🧾 {{ $invoice->invoice_number }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <span class="badge {{ $badge }}">{{ ucfirst($invoice->status) }}</span>
                <span class="badge badge-muted">{{ ucfirst($invoice->type) }}</span>
                <span style="font-size:13px;color:var(--text-muted);">{{ $invoice->client?->name }}</span>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('invoicing.index') }}" class="btn-outline">← All Invoices</a>
            <a href="{{ route('invoicing.pdf', $invoice) }}" target="_blank" class="btn-outline">🖨 Print</a>
            <a href="{{ route('invoicing.pdf', $invoice, ['download' => 1]) }}" class="btn-outline">⬇ Download</a>
            <a href="{{ route('invoicing.edit', $invoice) }}" class="btn-outline">Edit</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
        <div>
            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Details</div>
                @php $rows = [
                    'Client' => $invoice->client?->name ?? '—',
                    'Company' => $invoice->client?->company ?? '—',
                    'Issue Date' => optional($invoice->issue_date)->format('d M Y') ?? '—',
                    'Due Date' => optional($invoice->due_date)->format('d M Y') ?? '—',
                    'Vertical' => $invoice->vertical ? ucfirst($invoice->vertical) : '—',
                    'Currency' => $invoice->currency,
                    'Created By' => $invoice->createdBy?->name ?? '—',
                    'Sent At' => $invoice->sent_at?->format('d M Y H:i') ?? '—',
                    'Paid At' => $invoice->paid_at?->format('d M Y H:i') ?? '—',
                ]; @endphp
                @foreach($rows as $label => $value)
                <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-light);font-size:12px;">
                    <span style="color:var(--text-muted);width:80px;flex-shrink:0;">{{ $label }}</span><span>{{ $value }}</span>
                </div>
                @endforeach
                @if($invoice->notes)<p style="font-size:12px;color:var(--text-muted);margin-top:12px;"><strong>Notes:</strong> {{ $invoice->notes }}</p>@endif
                @if($invoice->terms)<p style="font-size:12px;color:var(--text-muted);margin-top:8px;"><strong>Terms:</strong> {{ $invoice->terms }}</p>@endif
            </div>

            @if($invoice->status !== 'paid')
            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Record Payment</div>
                <form method="POST" action="{{ route('invoicing.mark-paid', $invoice) }}">
                    @csrf
                    <div class="form-group"><label class="form-label">Amount ({{ $invoice->currency }})</label><input type="number" name="amount" class="form-input" value="{{ number_format($invoice->balance_due, 2, '.', '') }}" min="0.01" step="0.01" required></div>
                    <div class="form-group"><label class="form-label">Method</label>
                        <select name="method" class="form-input" required>
                            @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','mobile_money'=>'Mobile Money','cheque'=>'Cheque','card'=>'Card'] as $value=>$label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Reference</label><input type="text" name="reference" class="form-input" placeholder="Transaction ID / cheque no"></div>
                    <div class="form-group"><label class="form-label">Payment Date</label><input type="date" name="payment_date" class="form-input" value="{{ date('Y-m-d') }}"></div>
                    <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="2"></textarea></div>
                    <button type="submit" class="btn-gold" style="width:100%;font-size:12px;">✓ Record Payment</button>
                </form>
            </div>
            @endif

            <div class="card" style="padding:20px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Actions</div>
                @if($invoice->status !== 'paid' && $invoice->status !== 'sent')
                <form method="POST" action="{{ route('invoicing.send', $invoice) }}" style="margin-bottom:10px;">@csrf<button type="submit" class="btn-navy" style="width:100%;font-size:12px;">📤 Mark as Sent</button></form>
                @endif
                <form method="POST" action="{{ route('invoicing.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline" style="width:100%;font-size:12px;color:var(--error);">Delete Invoice</button>
                </form>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom:16px;">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:600;color:var(--navy);">Line Items</div>
                <table>
                    <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th style="text-align:right;">Unit Price</th><th style="text-align:right;">Total</th></tr></thead>
                    <tbody>
                    @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td style="text-align:right;">{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align:right;font-weight:600;">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">No line items</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div style="padding:16px 20px;max-width:330px;margin-left:auto;font-size:13px;">
                    <div style="display:flex;justify-content:space-between;padding:4px 0;color:var(--text-muted);"><span>Subtotal</span><span>{{ number_format($invoice->subtotal, 2) }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;color:var(--text-muted);"><span>Discount ({{ $invoice->discount_percent }}%)</span><span>−{{ number_format($invoice->discount_amount, 2) }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;color:var(--text-muted);"><span>VAT ({{ $invoice->tax_percent }}%)</span><span>{{ number_format($invoice->tax_amount, 2) }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:10px 0 0;margin-top:6px;border-top:2px solid var(--gold);font-weight:700;color:var(--navy);"><span>Total</span><span>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;color:var(--text-muted);"><span>Paid</span><span>{{ number_format($invoice->amount_paid, 2) }}</span></div>
                    <div style="display:flex;justify-content:space-between;padding:4px 0;font-weight:600;color:{{ $invoice->balance_due > 0 ? 'var(--error)' : 'var(--success)' }};"><span>Balance Due</span><span>{{ number_format($invoice->balance_due, 2) }}</span></div>
                </div>
            </div>

            <div class="card">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:600;color:var(--navy);">Payments ({{ $invoice->payments->count() }})</div>
                @forelse($invoice->payments as $payment)
                <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                    <div>
                        <div style="font-weight:600;color:var(--navy);">{{ $payment->payment_date->format('d M Y') }} · {{ ucfirst(str_replace('_',' ',$payment->method)) }}</div>
                        <div style="color:var(--text-muted);">{{ $payment->reference ?: 'No reference' }}@if($payment->recordedBy) · {{ $payment->recordedBy->name }}@endif</div>
                    </div>
                    <div style="font-weight:600;color:var(--success);">{{ $invoice->currency }} {{ number_format($payment->amount, 2) }}</div>
                </div>
                @empty
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">No payments recorded yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
