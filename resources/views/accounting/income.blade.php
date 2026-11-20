@extends('layouts.app')
@section('title', 'Income')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Accounting</div><h1 class="page-title">💰 Income / Payments</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('invoicing.index') }}" class="btn-gold">View Invoices</a>
            <a href="{{ route('accounting.index') }}" class="btn-outline">← Dashboard</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);">Received This Month</div>
            <div style="font-size:24px;font-weight:700;color:var(--success);">ZMW {{ number_format($total_month,2) }}</div>
        </div>
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);">Payment Count This Month</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $payments->total() }}</div>
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Payment Records</div>
        </div>
        @forelse($payments as $pay)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div>
                @if($pay->invoice)
                <a href="{{ route('invoicing.show', $pay->invoice) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $pay->invoice->invoice_number }}</a>
                <div style="font-size:12px;color:var(--text-muted);">{{ $pay->invoice->client?->name??'—' }}</div>
                @else
                <span style="font-weight:600;">Payment</span>
                @endif
                @if($pay->notes)<div style="font-size:11px;color:var(--text-light);">{{ $pay->notes }}</div>@endif
            </div>
            <div style="text-align:right;">
                <div style="font-weight:700;color:var(--success);">ZMW {{ number_format($pay->amount,2) }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ ucwords(str_replace('_',' ',$pay->method??'')) }} · {{ $pay->created_at->format('d M Y') }}</div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No payments recorded yet</div>
        @endforelse
        @if($payments->hasPages())
        <div style="padding:16px 20px;">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
