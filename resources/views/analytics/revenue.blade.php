@extends('layouts.app')
@section('title', 'Revenue Analytics')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Analytics</div><h1 class="page-title">💰 Revenue — 12-Month View</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('analytics.export', ['report' => 'revenue', 'format' => 'csv']) }}" class="btn-outline" style="font-size:12px;">↓ CSV</a>
            <a href="{{ route('analytics.export', ['report' => 'revenue', 'format' => 'json']) }}" class="btn-outline" style="font-size:12px;">↓ JSON</a>
            <a href="{{ route('analytics.index') }}" class="btn-outline">← Dashboard</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="card" style="margin-bottom:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Monthly Revenue vs Expenses</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:rgba(16,7,54,0.04);">
                        @foreach(['Month','Invoiced','Collected','Expenses','Net'] as $h)
                        <th style="padding:10px 16px;text-align:{{ $loop->index>0?'right':'left' }};font-size:11px;color:var(--text-muted);font-weight:600;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m)
                    @php $net = $m['collected'] - $m['expenses']; @endphp
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:10px 16px;font-weight:600;color:var(--navy);">{{ $m['label'] }}</td>
                        <td style="padding:10px 16px;text-align:right;">ZMW {{ number_format($m['invoiced'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--success);">ZMW {{ number_format($m['collected'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">ZMW {{ number_format($m['expenses'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;color:{{ $net>=0?'var(--success)':'var(--error)' }};">ZMW {{ number_format(abs($net),2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($by_vertical->count())
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Revenue by Vertical — {{ now()->year }}</div>
        </div>
        @foreach($by_vertical as $row)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;font-size:13px;">
            <div style="font-weight:600;color:var(--navy);">{{ ucfirst($row->vertical??'Unassigned') }}</div>
            <div style="font-weight:700;color:var(--success);">ZMW {{ number_format($row->total,2) }}</div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
