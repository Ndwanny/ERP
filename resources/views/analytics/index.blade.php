@extends('layouts.app')
@section('title', 'Analytics')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Business Intelligence</div><h1 class="page-title">📈 Analytics</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('analytics.revenue') }}" class="btn-outline">Revenue</a>
            <a href="{{ route('analytics.production') }}" class="btn-outline">Production</a>
            <a href="{{ route('analytics.clients') }}" class="btn-outline">Clients</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Revenue This Month</div>
            <div style="font-size:24px;font-weight:700;color:var(--success);">ZMW {{ number_format($revenue_this_month,0) }}</div>
            @if($revenue_last_month > 0)
            @php $change = (($revenue_this_month - $revenue_last_month) / $revenue_last_month) * 100; @endphp
            <div style="font-size:12px;color:{{ $change>=0?'var(--success)':'var(--error)' }};margin-top:4px;">{{ $change>=0?'+':'' }}{{ number_format($change,1) }}% vs last month</div>
            @endif
        </div>
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Active Production Orders</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $orders_active }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $orders_completed }} completed this month</div>
        </div>
        <div class="card" style="padding:20px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Outstanding Receivables</div>
            <div style="font-size:24px;font-weight:700;color:var(--gold);">ZMW {{ number_format($outstanding,0) }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $active_clients }} active clients</div>
        </div>
    </div>

    {{-- 6-month trend table --}}
    <div class="card" style="margin-bottom:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">6-Month Performance</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:rgba(16,7,54,0.04);">
                        @foreach(['Month','Revenue','Expenses','Net','Orders'] as $h)
                        <th style="padding:10px 16px;text-align:{{ $loop->index>0?'right':'left' }};font-size:11px;color:var(--text-muted);font-weight:600;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m)
                    @php $net = $m['revenue'] - $m['expenses']; @endphp
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:10px 16px;font-weight:600;color:var(--navy);">{{ $m['label'] }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--success);">ZMW {{ number_format($m['revenue'],0) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">ZMW {{ number_format($m['expenses'],0) }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:600;color:{{ $net>=0?'var(--success)':'var(--error)' }};">ZMW {{ number_format(abs($net),0) }}</td>
                        <td style="padding:10px 16px;text-align:right;">{{ $m['orders'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top clients --}}
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Top Clients by Revenue (All Time)</div>
        </div>
        @forelse($top_clients as $i => $tc)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="width:24px;height:24px;border-radius:50%;background:rgba(249,176,64,{{ 1 - ($i * 0.15) }});display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--navy);">{{ $i+1 }}</span>
                <div>
                    <div style="font-weight:600;color:var(--navy);">{{ $tc->client?->name??'Unknown' }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $tc->client?->company??'' }}</div>
                </div>
            </div>
            <div style="font-weight:700;color:var(--success);">ZMW {{ number_format($tc->total_revenue,2) }}</div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No paid invoices yet</div>
        @endforelse
    </div>

    <div class="card" style="margin-top:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Export Reports</div>
        </div>
        @foreach(['revenue'=>'💰 Revenue','production'=>'🏭 Production','clients'=>'👥 Clients','staff'=>'👔 Staff','inventory'=>'🧵 Inventory'] as $key=>$label)
        <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:13px;font-weight:600;color:var(--navy);">{{ $label }}</span>
            <span style="display:flex;gap:8px;">
                <a href="{{ route('analytics.export', ['report' => $key, 'format' => 'csv']) }}" class="btn-outline" style="font-size:11px;padding:5px 12px;">CSV</a>
                <a href="{{ route('analytics.export', ['report' => $key, 'format' => 'json']) }}" class="btn-outline" style="font-size:11px;padding:5px 12px;">JSON</a>
            </span>
        </div>
        @endforeach
    </div>
</div>
@endsection
