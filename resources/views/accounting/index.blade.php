@extends('layouts.app')
@section('title', 'Accounting')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Finance</div><h1 class="page-title">📊 Accounting</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('accounting.expenses') }}" class="btn-gold">+ Expense</a>
            <a href="{{ route('accounting.reports.profit-loss') }}" class="btn-outline">P&L Report</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Income This Month</div>
            <div style="font-size:22px;font-weight:700;color:var(--success);">ZMW {{ number_format($income_this_month,2) }}</div>
        </div>
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Expenses This Month</div>
            <div style="font-size:22px;font-weight:700;color:var(--error);">ZMW {{ number_format($expenses_this_month,2) }}</div>
        </div>
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Net This Month</div>
            @php $net = $income_this_month - $expenses_this_month; @endphp
            <div style="font-size:22px;font-weight:700;color:{{ $net>=0?'var(--success)':'var(--error)' }};">ZMW {{ number_format(abs($net),2) }}{{ $net<0?' (loss)':'' }}</div>
        </div>
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Outstanding Receivables</div>
            <div style="font-size:22px;font-weight:700;color:var(--gold);">ZMW {{ number_format($outstanding,2) }}</div>
        </div>
    </div>

    {{-- Quick nav --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px;">
        @foreach([['Income','💰',route('accounting.income')],['Expenses','📉',route('accounting.expenses')],['Ledger','📒',route('accounting.ledger')],['P&L','📈',route('accounting.reports.profit-loss')],['ZRA Report','🇿🇲',route('accounting.reports.zra')]] as [$l,$icon,$url])
        <a href="{{ $url }}" class="card" style="padding:16px;text-decoration:none;text-align:center;">
            <div style="font-size:22px;margin-bottom:6px;">{{ $icon }}</div>
            <div style="font-size:12px;font-weight:600;color:var(--navy);">{{ $l }}</div>
        </a>
        @endforeach
    </div>

    {{-- 6-month trend --}}
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">6-Month Financial Trend</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:rgba(16,7,54,0.04);">
                        <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);font-weight:600;">Month</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">Income</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">Expenses</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m)
                    @php $mn = $m['income'] - $m['expenses']; @endphp
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:10px 16px;font-weight:600;color:var(--navy);">{{ $m['label'] }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--success);">ZMW {{ number_format($m['income'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">ZMW {{ number_format($m['expenses'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:600;color:{{ $mn>=0?'var(--success)':'var(--error)' }};">ZMW {{ number_format(abs($mn),2) }}{{ $mn<0?' ▼':' ▲' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
