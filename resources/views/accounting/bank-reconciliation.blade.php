@extends('layouts.app')
@section('title', 'Bank Reconciliation')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Accounting</div><h1 class="page-title">🏦 Bank Reconciliation</h1></div>
        <a href="{{ route('accounting.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div class="card" style="padding:20px;margin-bottom:20px;">
        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Total Payments Recorded (System)</div>
        <div style="font-size:28px;font-weight:700;color:var(--navy);">ZMW {{ number_format($total,2) }}</div>
        <p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Compare against your bank statement to identify discrepancies. Cross-reference each payment below with your bank records.</p>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Recent Payments (Last 50)</div>
        </div>
        @forelse($payments as $pay)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div>
                @if($pay->invoice)<a href="{{ route('invoicing.show', $pay->invoice) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $pay->invoice->invoice_number }}</a>@else<span style="font-weight:600;">Payment</span>@endif
                <div style="font-size:11px;color:var(--text-muted);">{{ $pay->invoice?->client?->name??'—' }} · {{ ucwords(str_replace('_',' ',$pay->method??'')) }}</div>
                @if($pay->notes)<div style="font-size:11px;color:var(--text-light);">{{ $pay->notes }}</div>@endif
            </div>
            <div style="text-align:right;">
                <div style="font-weight:700;color:var(--success);">ZMW {{ number_format($pay->amount,2) }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $pay->created_at->format('d M Y') }}</div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No payment records found</div>
        @endforelse
    </div>
</div>
@endsection
