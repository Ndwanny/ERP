@extends('layouts.app')
@section('title', 'Production Analytics')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Analytics</div><h1 class="page-title">🏭 Production Metrics</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('analytics.export', ['report' => 'production', 'format' => 'csv']) }}" class="btn-outline" style="font-size:12px;">↓ CSV</a>
            <a href="{{ route('analytics.export', ['report' => 'production', 'format' => 'json']) }}" class="btn-outline" style="font-size:12px;">↓ JSON</a>
            <a href="{{ route('analytics.index') }}" class="btn-outline">← Dashboard</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Orders by Stage</div>
            </div>
            @foreach(['brief','design','production','delivery','completed','cancelled'] as $stage)
            <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <div style="font-weight:500;color:var(--navy);">{{ ucfirst($stage) }}</div>
                <span class="badge badge-muted">{{ $by_stage[$stage]??0 }} orders</span>
            </div>
            @endforeach
        </div>

        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Orders by Priority</div>
            </div>
            @foreach(['urgent','high','normal','low'] as $p)
            @php $colors=['urgent'=>'badge-error','high'=>'badge-gold','normal'=>'badge-muted','low'=>'badge-muted']; @endphp
            <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <div style="font-weight:500;color:var(--navy);">{{ ucfirst($p) }}</div>
                <span class="badge {{ $colors[$p]??'badge-muted' }}">{{ $by_priority[$p]??0 }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Monthly Production Volume</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:rgba(16,7,54,0.04);">
                        <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);font-weight:600;">Month</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">Created</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthly as $m)
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:10px 16px;font-weight:600;color:var(--navy);">{{ $m['label'] }}</td>
                        <td style="padding:10px 16px;text-align:right;">{{ $m['created'] }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--success);">{{ $m['completed'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
