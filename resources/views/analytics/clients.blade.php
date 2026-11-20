@extends('layouts.app')
@section('title', 'Client Analytics')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Analytics</div><h1 class="page-title">👥 Client Analytics</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('analytics.export', ['report' => 'clients', 'format' => 'csv']) }}" class="btn-outline" style="font-size:12px;">↓ CSV</a>
            <a href="{{ route('analytics.export', ['report' => 'clients', 'format' => 'json']) }}" class="btn-outline" style="font-size:12px;">↓ JSON</a>
            <a href="{{ route('analytics.index') }}" class="btn-outline">← Dashboard</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Clients by Type</div>
            </div>
            @foreach($by_type as $type=>$count)
            <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;font-size:13px;">
                <div style="font-weight:500;color:var(--navy);">{{ ucfirst($type) }}</div>
                <span class="badge badge-muted">{{ $count }}</span>
            </div>
            @endforeach
        </div>
        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Clients by Status</div>
            </div>
            @foreach($by_status as $status=>$count)
            <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;font-size:13px;">
                <div style="font-weight:500;color:var(--navy);">{{ ucfirst($status) }}</div>
                <span class="badge badge-muted">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Top 10 Clients by Revenue (All Time)</div>
        </div>
        @forelse($top_clients as $i=>$tc)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:24px;height:24px;border-radius:50%;background:rgba(249,176,64,{{ max(0.15, 1 - ($i*0.08)) }});display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--navy);">{{ $i+1 }}</span>
                <div>
                    <div style="font-weight:600;color:var(--navy);">{{ $tc->client?->name??'Unknown' }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $tc->invoice_count }} invoice{{ $tc->invoice_count!=1?'s':'' }}</div>
                </div>
            </div>
            <div style="font-weight:700;color:var(--success);">ZMW {{ number_format($tc->total_revenue,2) }}</div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No revenue data yet</div>
        @endforelse
    </div>
</div>
@endsection
