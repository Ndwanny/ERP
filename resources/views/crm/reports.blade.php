@extends('layouts.app')
@section('title', 'CRM Reports')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">CRM</div>
            <h1 class="page-title">🤝 CRM Reports</h1>
        </div>
        <a href="{{ route('crm.index') }}" class="btn-outline">← Back</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
        @foreach(['Total Clients'=>$stats['total'],'Active Clients'=>$stats['active'],'Leads'=>$stats['leads'],'VIP / Enterprise'=>$stats['vip']] as $label => $val)
        <div class="card" style="padding:18px 20px;">
            <div style="font-size:28px;font-weight:700;color:var(--navy);">{{ $val }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $label }}</div>
        </div>
        @endforeach
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card" style="padding:20px;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);margin-bottom:16px;">By Type</div>
            @foreach($byType as $type => $count)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                <span>{{ ucfirst($type) }}</span>
                <span style="font-weight:600;color:var(--navy);">{{ $count }}</span>
            </div>
            @endforeach
        </div>
        <div class="card" style="padding:20px;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);margin-bottom:16px;">By Status</div>
            @foreach($byStatus as $status => $count)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                <span>{{ ucfirst($status) }}</span>
                <span style="font-weight:600;color:var(--navy);">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card" style="margin-top:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:600;color:var(--navy);">Top Clients by Lifetime Value</div>
        <table>
            <thead><tr><th>Client</th><th>Status</th><th style="text-align:right;">Lifetime Value</th></tr></thead>
            <tbody>
            @forelse($topClients as $client)
            <tr>
                <td><a href="{{ route('crm.clients.show', $client) }}" style="color:var(--navy);font-weight:600;text-decoration:none;">{{ $client->name }}</a><br><span style="font-size:11px;color:var(--text-muted);">{{ $client->company }}</span></td>
                <td><span class="badge badge-muted">{{ ucfirst($client->status) }}</span></td>
                <td style="text-align:right;font-weight:600;">ZMW {{ number_format($client->lifetime_value,2) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-muted);">No client data yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
