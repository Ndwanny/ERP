@extends('layouts.app')
@section('title', 'Staff Performance')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Analytics</div><h1 class="page-title">👔 Staff Performance</h1></div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('analytics.export', ['report' => 'staff', 'format' => 'csv']) }}" class="btn-outline" style="font-size:12px;">↓ CSV</a>
            <a href="{{ route('analytics.export', ['report' => 'staff', 'format' => 'json']) }}" class="btn-outline" style="font-size:12px;">↓ JSON</a>
            <a href="{{ route('analytics.index') }}" class="btn-outline">← Dashboard</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Attendance — {{ now()->format('F Y') }}</div>
        </div>
        @forelse($employees as $emp)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div>
                <a href="{{ route('hr.employees.show', $emp) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $emp->first_name }} {{ $emp->last_name }}</a>
                <div style="font-size:12px;color:var(--text-muted);">{{ $emp->job_title }}@if($emp->department) · {{ $emp->department?->name }}@endif</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:14px;font-weight:600;color:var(--navy);">{{ $emp->attendance_count }} days</div>
                <div style="font-size:11px;color:var(--text-muted);">this month</div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No active employees</div>
        @endforelse
    </div>
</div>
@endsection
