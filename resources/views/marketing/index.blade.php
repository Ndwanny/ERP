@extends('layouts.app')
@section('title', 'Marketing')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Growth</div><h1 class="page-title">📣 Marketing</h1></div>
        <a href="{{ route('marketing.campaigns.create') }}" class="btn-gold">+ Campaign</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
        @foreach(['Campaigns'=>[$total_campaigns,'📢'],'Active'=>[$active_campaigns,'▶'],'Total Leads'=>[$total_leads,'🎯'],'New Leads'=>[$new_leads,'⭐']] as $l=>[$v,$icon])
        <div class="card" style="padding:20px;text-align:center;">
            <div style="font-size:20px;margin-bottom:4px;">{{ $icon }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">{{ $l }}</div>
            <div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $v }}</div>
        </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
        @foreach([['Campaigns','📢',route('marketing.campaigns')],['Social Posts','📱',route('marketing.social-scheduler')],['Events','🎪',route('marketing.events')],['Leads','🎯',route('marketing.leads')]] as [$l,$icon,$url])
        <a href="{{ $url }}" class="card" style="padding:20px;text-decoration:none;text-align:center;">
            <div style="font-size:26px;margin-bottom:8px;">{{ $icon }}</div>
            <div style="font-size:13px;font-weight:600;color:var(--navy);">{{ $l }}</div>
        </a>
        @endforeach
    </div>
</div>
@endsection
