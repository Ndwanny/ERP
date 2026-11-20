@extends('layouts.app')
@section('title', 'Inventory Analytics')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Analytics</div><h1 class="page-title">🧵 Inventory Trends</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('analytics.export', ['report' => 'inventory', 'format' => 'csv']) }}" class="btn-outline" style="font-size:12px;">↓ CSV</a>
            <a href="{{ route('analytics.export', ['report' => 'inventory', 'format' => 'json']) }}" class="btn-outline" style="font-size:12px;">↓ JSON</a>
            <a href="{{ route('analytics.index') }}" class="btn-outline">← Dashboard</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:20px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Low / Out of Stock Items</div>
            <div style="font-size:32px;font-weight:700;color:{{ $low_stock>0?'var(--error)':'var(--success)' }};">{{ $low_stock }}</div>
        </div>
        <div class="card" style="padding:20px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">Total Inventory Value</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">ZMW {{ number_format($total_value,2) }}</div>
        </div>
        <div class="card" style="padding:20px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);">In Stock Items</div>
            <div style="font-size:32px;font-weight:700;color:var(--success);">{{ $by_status['in_stock']??0 }}</div>
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Status Breakdown</div>
        </div>
        @foreach(['in_stock'=>['In Stock','badge-success'],'low_stock'=>['Low Stock','badge-gold'],'out_of_stock'=>['Out of Stock','badge-error']] as $key=>[$label,$badge])
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <span class="badge {{ $badge }}">{{ $label }}</span>
            <span style="font-weight:600;color:var(--navy);">{{ $by_status[$key]??0 }} items</span>
        </div>
        @endforeach

        <div style="padding:16px 20px;">
            <a href="{{ route('inventory.index') }}" class="btn-gold" style="font-size:12px;">View Inventory →</a>
            <a href="{{ route('inventory.low-stock') }}" class="btn-outline" style="font-size:12px;margin-left:8px;">View Alerts →</a>
        </div>
    </div>
</div>
@endsection
