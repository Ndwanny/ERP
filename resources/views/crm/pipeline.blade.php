@extends('layouts.app')
@section('title', 'CRM Pipeline')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">CRM</div>
            <h1 class="page-title">🤝 Sales Pipeline</h1>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('crm.index') }}" class="btn-outline">List View</a>
            <a href="{{ route('crm.clients.create') }}" class="btn-gold">+ New Client</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @php
        $stages = ['lead'=>'Leads','prospect'=>'Prospects','active'=>'Active Clients','dormant'=>'Dormant','lost'=>'Lost'];
        $stageColors = ['lead'=>'#F9B040','prospect'=>'#6366f1','active'=>'#059669','dormant'=>'#9896a8','lost'=>'#c0392b'];
    @endphp
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
        @foreach(['Total Clients'=>$stats['total'],'Active'=>$stats['active'],'Open Leads'=>$stats['leads']] as $label=>$value)
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $value }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $label }}</div>
        </div>
        @endforeach
    </div>
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;min-width:900px;overflow-x:auto;">
        @foreach($stages as $key => $label)
        @php $col = $pipeline[$key] ?? collect(); @endphp
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div style="font-size:12px;font-weight:600;color:{{ $stageColors[$key] }};">{{ $label }}</div>
                <span style="background:{{ $stageColors[$key] }}22;color:{{ $stageColors[$key] }};font-size:11px;font-weight:600;padding:2px 8px;border-radius:12px;">{{ $col->count() }}</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @forelse($col as $client)
                <a href="{{ route('crm.clients.show', $client) }}" style="text-decoration:none;">
                    <div class="card" style="padding:12px;transition:box-shadow 0.15s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(16,7,54,0.1)'" onmouseout="this.style.boxShadow=''">
                        <div style="font-size:13px;font-weight:500;color:var(--navy);margin-bottom:4px;">{{ $client->name }}</div>
                        @if($client->company)<div style="font-size:11px;color:var(--text-muted);">{{ $client->company }}</div>@endif
                        <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center;">
                            <span class="badge badge-muted" style="font-size:10px;">{{ ucfirst($client->type) }}</span>
                            @if($client->tier !== 'standard')
                                <span class="badge badge-gold" style="font-size:10px;">{{ ucfirst($client->tier) }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @empty
                <div style="padding:20px 0;text-align:center;font-size:12px;color:var(--text-light);">Empty</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
