@extends('layouts.app')
@section('title', 'Campaigns')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Marketing</div><h1 class="page-title">📢 Campaigns</h1></div>
        <a href="{{ route('marketing.campaigns.create') }}" class="btn-gold">+ New Campaign</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    <div class="card">
        @php $statusColors=['draft'=>'badge-muted','scheduled'=>'badge-navy','active'=>'badge-success','paused'=>'badge-gold','completed'=>'badge-navy']; @endphp
        @forelse($campaigns as $camp)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;align-items:center;gap:12px;font-size:13px;">
            <div>
                <a href="{{ route('marketing.campaigns.show', $camp) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $camp->name }}</a>
                <div style="font-size:12px;color:var(--text-muted);">{{ ucwords(str_replace('_',' ',$camp->type)) }}</div>
            </div>
            <span class="badge {{ $statusColors[$camp->status]??'badge-muted' }}">{{ ucfirst($camp->status) }}</span>
            <div style="font-size:12px;color:var(--text-muted);">{{ optional($camp->start_date)->format('d M Y') }} → {{ optional($camp->end_date)->format('d M Y')??'Ongoing' }}</div>
            <div>{{ $camp->leads_count }} lead{{ $camp->leads_count!=1?'s':'' }}</div>
            <div style="text-align:right;font-size:12px;">@if($camp->budget)<div>Budget: K{{ number_format($camp->budget,0) }}</div>@endif</div>
        </div>
        @empty
        <div style="padding:60px;text-align:center;color:var(--text-muted);">No campaigns yet. <a href="{{ route('marketing.campaigns.create') }}" style="color:var(--gold);">Create one</a>.</div>
        @endforelse
        @if($campaigns->hasPages())<div style="padding:16px 20px;">{{ $campaigns->links() }}</div>@endif
    </div>
</div>
@endsection
