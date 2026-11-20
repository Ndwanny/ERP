@extends('layouts.app')
@section('title', 'Profit & Loss')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Accounting</div><h1 class="page-title">📈 Profit & Loss Statement</h1></div>
        <a href="{{ route('accounting.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div class="card" style="margin-bottom:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">6-Month P&L Summary</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:rgba(16,7,54,0.04);">
                        @foreach(['Month','Income','Expenses','Gross Profit','Margin'] as $h)
                        <th style="padding:10px 16px;text-align:{{ $loop->index>0?'right':'left' }};font-size:11px;color:var(--text-muted);font-weight:600;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m)
                    @php $margin = $m['income'] > 0 ? round(($m['profit']/$m['income'])*100,1) : 0; @endphp
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:10px 16px;font-weight:600;color:var(--navy);">{{ $m['label'] }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--success);">ZMW {{ number_format($m['income'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">ZMW {{ number_format($m['expenses'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:600;color:{{ $m['profit']>=0?'var(--success)':'var(--error)' }};">ZMW {{ number_format(abs($m['profit']),2) }}{{ $m['profit']<0?' (L)':'' }}</td>
                        <td style="padding:10px 16px;text-align:right;font-size:12px;color:{{ $margin>=0?'var(--success)':'var(--error)' }};">{{ $margin }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Expenses by Category — {{ now()->year }}</div>
        </div>
        @forelse($by_category as $cat)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div style="font-weight:600;color:var(--navy);">{{ ucfirst($cat->category??'Uncategorised') }}</div>
            <div style="font-weight:700;color:var(--error);">ZMW {{ number_format($cat->total,2) }}</div>
        </div>
        @empty
        <div style="padding:24px;text-align:center;color:var(--text-muted);">No expense data</div>
        @endforelse
    </div>
</div>
@endsection
