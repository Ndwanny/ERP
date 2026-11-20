@extends('layouts.app')
@section('title', 'Smart Invoicing')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">Module 01</div>
            <h1 class="page-title">🧾 Smart Invoicing</h1>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('invoicing.quotes.create') }}" class="btn-outline">+ New Quote</a>
            <a href="{{ route('invoicing.create') }}" class="btn-gold">+ New Invoice</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Total</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $stats['total'] }}</div>
        </div>
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Drafts</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $stats['draft'] }}</div>
        </div>
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Sent / Viewed</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $stats['sent'] }}</div>
        </div>
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Paid</div>
            <div style="font-size:24px;font-weight:700;color:var(--success);">{{ $stats['paid'] }}</div>
        </div>
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Overdue</div>
            <div style="font-size:24px;font-weight:700;color:var(--error);">{{ $stats['overdue'] }}</div>
        </div>
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Outstanding</div>
            <div style="font-size:18px;font-weight:700;color:var(--gold-dark);">K{{ number_format($stats['outstanding'], 2) }}</div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif

    <div class="card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="GET" action="{{ route('invoicing.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div class="form-group" style="margin:0;flex:1;min-width:180px;"><label class="form-label">Search</label><input type="text" name="q" class="form-input" value="{{ request('q') }}" placeholder="Invoice number or client…"></div>
            <div class="form-group" style="margin:0;min-width:160px;"><label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">All statuses</option>
                    @foreach(['draft','sent','viewed','partial','paid','overdue','cancelled'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-navy">Filter</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th><th>Client</th><th>Type</th><th>Amount</th><th>Balance</th><th>Status</th><th>Due Date</th><th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoices as $invoice)
                @php
                $badge = ['draft'=>'badge-muted','sent'=>'badge-gold','viewed'=>'badge-navy','partial'=>'badge-gold','paid'=>'badge-success','overdue'=>'badge-error','cancelled'=>'badge-muted'][$invoice->status] ?? 'badge-muted';
                @endphp
                <tr>
                    <td><a href="{{ route('invoicing.show', $invoice) }}" style="color:#C9A84C;">{{ $invoice->invoice_number }}</a></td>
                    <td>{{ $invoice->client?->name }}</td>
                    <td style="font-size:11px;color:var(--text-muted);">{{ ucfirst($invoice->type) }}</td>
                    <td>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                    <td>{{ number_format($invoice->balance_due, 2) }}</td>
                    <td><span class="badge {{ $badge }}">{{ ucfirst($invoice->status) }}</span></td>
                    <td>{{ $invoice->due_date?->format('d M Y') }}</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <a href="{{ route('invoicing.show', $invoice) }}" style="color:var(--gold-dark);font-size:12px;">View</a>
                        <a href="{{ route('invoicing.edit', $invoice) }}" style="color:var(--text-muted);font-size:12px;margin-left:8px;">Edit</a>
                        <a href="{{ route('invoicing.pdf', $invoice) }}" target="_blank" style="color:var(--text-muted);font-size:12px;margin-left:8px;">PDF</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">No invoices yet</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())<div style="padding:16px 20px;">{{ $invoices->links() }}</div>@endif
    </div>
</div>
@endsection
