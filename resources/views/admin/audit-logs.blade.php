@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Admin</div><h1 class="page-title">📋 Audit Logs</h1></div>
        <a href="{{ route('admin.index') }}" class="btn-outline">← Admin</a>
    </div>
</div>
<div class="page-body">
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Activity Log</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $logs->total() }} entries</div>
        </div>
        @forelse($logs as $log)
        <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);font-size:13px;">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <span style="font-weight:600;color:var(--navy);">{{ $log->user?->first_name??'System' }} {{ $log->user?->last_name??'' }}</span>
                    <span style="margin:0 6px;color:var(--text-muted);">·</span>
                    <span style="font-family:monospace;font-size:11px;background:rgba(16,7,54,0.06);padding:2px 6px;border-radius:4px;">{{ $log->action }}</span>
                    @if($log->module)
                    <span style="margin:0 4px;color:var(--text-muted);">in</span>
                    <span style="font-size:11px;color:var(--text-muted);">{{ ucfirst($log->module) }}</span>
                    @endif
                </div>
                <span style="font-size:11px;color:var(--text-light);white-space:nowrap;margin-left:12px;">{{ $log->created_at->format('d M Y H:i') }}</span>
            </div>
            @if($log->ip_address)<div style="font-size:11px;color:var(--text-light);margin-top:2px;">IP: {{ $log->ip_address }}</div>@endif
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No audit log entries yet</div>
        @endforelse
        @if($logs->hasPages())
        <div style="padding:16px 20px;">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
