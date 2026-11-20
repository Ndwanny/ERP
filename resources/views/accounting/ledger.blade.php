@extends('layouts.app')
@section('title', 'General Ledger')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Accounting</div><h1 class="page-title">📒 General Ledger</h1>
            <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">{{ \Carbon\Carbon::createFromFormat('Y-m',$month)->format('F Y') }}</div>
        </div>
        <a href="{{ route('accounting.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:20px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Total Income</div>
            <div style="font-size:22px;font-weight:700;color:var(--success);">ZMW {{ number_format($total_income,2) }}</div>
        </div>
        <div class="card" style="padding:20px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Total Expenses</div>
            <div style="font-size:22px;font-weight:700;color:var(--error);">ZMW {{ number_format($total_expenses,2) }}</div>
        </div>
        <div class="card" style="padding:20px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Net</div>
            <div style="font-size:22px;font-weight:700;color:{{ $net>=0?'var(--success)':'var(--error)' }};">ZMW {{ number_format(abs($net),2) }}{{ $net<0?' (loss)':'' }}</div>
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Transactions — {{ \Carbon\Carbon::createFromFormat('Y-m',$month)->format('F Y') }}</div>
        </div>
        @forelse($entries as $entry)
        @php $isIncome = $entry->entry_type === 'income'; @endphp
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;background:{{ $isIncome?'rgba(39,174,96,0.1)':'rgba(231,76,60,0.1)' }};">{{ $isIncome?'↑':'↓' }}</div>
                <div>
                    <div style="font-weight:600;color:var(--navy);">{{ $isIncome?'Payment Received':ucfirst($entry->category??'Expense') }}</div>
                    @if($entry->notes)<div style="font-size:11px;color:var(--text-muted);">{{ $entry->notes }}</div>@endif
                    <div style="font-size:11px;color:var(--text-light);">{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y H:i') }}@if($entry->payment_method) · {{ ucwords(str_replace('_',' ',$entry->payment_method)) }}@endif</div>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:700;color:{{ $isIncome?'var(--success)':'var(--error)' }};">{{ $isIncome?'+':'−' }}ZMW {{ number_format($entry->amount,2) }}</div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No transactions for this month</div>
        @endforelse
    </div>
</div>
@endsection
