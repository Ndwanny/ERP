@extends('layouts.app')
@section('title', 'Projects')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Operations</div><h1 class="page-title">📐 Project Management</h1></div>
        <a href="{{ route('projects.create') }}" class="btn-gold">+ New Project</a>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
        @foreach(['Total'=>['total','badge-muted'],'Active'=>['active','badge-success'],'Completed'=>['completed','badge-navy'],'Overdue'=>['overdue','badge-error']] as $label=>[$key,$badge])
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">{{ $label }}</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $stats[$key] }}</div>
        </div>
        @endforeach
    </div>

    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif

    <div class="card">
        @forelse($projects as $project)
        @php
        $statusColors=['planning'=>'badge-muted','active'=>'badge-success','on_hold'=>'badge-gold','completed'=>'badge-navy','cancelled'=>'badge-error'];
        $overdue = $project->status==='active' && $project->end_date && $project->end_date->isPast();
        @endphp
        <div style="padding:16px 20px;border-bottom:1px solid var(--border-light);display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;align-items:center;gap:12px;font-size:13px;">
            <div>
                <a href="{{ route('projects.show', $project) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $project->project_number }}</a>
                <div style="color:var(--text-muted);font-size:12px;">{{ $project->name }}</div>
                @if($project->location)<div style="font-size:11px;color:var(--text-light);">📍 {{ $project->location }}</div>@endif
            </div>
            <div>
                <div>{{ $project->client?->name??'—' }}</div>
                @if($project->client?->company)<div style="font-size:11px;color:var(--text-muted);">{{ $project->client->company }}</div>@endif
            </div>
            <div>
                <span class="badge {{ $statusColors[$project->status]??'badge-muted' }}">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ ucfirst($project->type) }}</div>
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="flex:1;background:var(--border);border-radius:4px;height:6px;">
                        <div style="background:var(--gold);width:{{ $project->completion_percent }}%;height:6px;border-radius:4px;"></div>
                    </div>
                    <span style="font-size:11px;color:var(--text-muted);white-space:nowrap;">{{ $project->completion_percent }}%</span>
                </div>
            </div>
            <div style="text-align:right;font-size:12px;">
                @if($project->end_date)
                <div style="color:{{ $overdue?'var(--error)':'var(--text-muted)' }};">{{ $project->end_date->format('d M Y') }}@if($overdue) ⚠️@endif</div>
                @endif
                @if($project->budget)<div style="color:var(--text-light);">Budget: K{{ number_format($project->budget,0) }}</div>@endif
            </div>
        </div>
        @empty
        <div style="padding:60px;text-align:center;color:var(--text-muted);">No projects yet. <a href="{{ route('projects.create') }}" style="color:var(--gold);">Create the first one</a>.</div>
        @endforelse
        @if($projects->hasPages())<div style="padding:16px 20px;">{{ $projects->links() }}</div>@endif
    </div>
</div>
@endsection
