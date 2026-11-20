@extends('layouts.app')
@section('title', 'Database Status')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Super Admin</div><h1 class="page-title">🗄️ Database Status</h1></div>
        <a href="{{ route('admin.index') }}" class="btn-outline">← Admin</a>
    </div>
</div>

<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    @if(session('info'))<div class="alert alert-warning">{{ session('info') }}</div>@endif

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:18px;">
            <div style="font-size:11px;color:var(--text-muted);">Connection</div>
            <div style="font-size:17px;font-weight:700;color:var(--navy);margin-top:4px;">{{ $connection }}</div>
            <div style="font-size:11px;color:var(--text-light);margin-top:2px;">Driver: {{ $driver }}</div>
        </div>
        <div class="card" style="padding:18px;">
            <div style="font-size:11px;color:var(--text-muted);">Database Name</div>
            <div style="font-size:13px;font-weight:600;color:var(--navy);margin-top:6px;word-break:break-all;">{{ $databaseName }}</div>
        </div>
        <div class="card" style="padding:18px;">
            <div style="font-size:11px;color:var(--text-muted);">Migrations</div>
            <div style="font-size:17px;font-weight:700;color:var(--success);margin-top:4px;">{{ $migrationsRan }} / {{ count($migrations) }}</div>
            <div style="font-size:11px;color:var(--text-light);margin-top:2px;">{{ $migrationsPending }} pending</div>
        </div>
        <div class="card" style="padding:18px;">
            <div style="font-size:11px;color:var(--text-muted);">Storage Writable</div>
            <div style="margin-top:6px;"><span class="badge {{ $storageWritable ? 'badge-success' : 'badge-error' }}">{{ $storageWritable ? 'Yes' : 'No' }}</span></div>
            <div style="font-size:11px;color:var(--text-light);margin-top:6px;">Last backup: {{ $lastBackup ? \Carbon\Carbon::parse($lastBackup)->format('d M Y H:i') : 'Never' }}</div>
        </div>
    </div>

    <div class="card" style="padding:20px;margin-bottom:20px;">
        <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:16px;text-transform:uppercase;letter-spacing:0.1em;">Environment</h3>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
            <div style="font-size:13px;"><span style="color:var(--text-muted);">Cache driver:</span> <strong style="color:var(--navy);">{{ $cacheDriver }}</strong></div>
            <div style="font-size:13px;"><span style="color:var(--text-muted);">Session driver:</span> <strong style="color:var(--navy);">{{ $sessionDriver }}</strong></div>
            <div style="font-size:13px;"><span style="color:var(--text-muted);">Queue driver:</span> <strong style="color:var(--navy);">{{ $queueDriver }}</strong></div>
            <div style="font-size:13px;"><span style="color:var(--text-muted);">PHP version:</span> <strong style="color:var(--navy);">{{ $phpVersion }}</strong></div>
            <div style="font-size:13px;"><span style="color:var(--text-muted);">Laravel version:</span> <strong style="color:var(--navy);">{{ $laravelVersion }}</strong></div>
        </div>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
            @foreach($storage as $path)
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;padding:4px 0;">
                <span style="color:var(--text-muted);font-family:monospace;word-break:break-all;">{{ $path['label'] }}</span>
                <span class="badge {{ $path['writable'] ? 'badge-success' : 'badge-error' }}">{{ $path['writable'] ? 'Writable' : 'Not writable' }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Migration Status</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $migrationsRan }} ran · {{ $migrationsPending }} pending</div>
        </div>
        <table>
            <thead><tr><th>Migration</th><th style="text-align:right;">Status</th></tr></thead>
            <tbody>
            @forelse($migrations as $migration)
                <tr>
                    <td style="font-family:monospace;font-size:12px;color:var(--navy);">{{ $migration['name'] }}</td>
                    <td style="text-align:right;"><span class="badge {{ $migration['ran'] ? 'badge-success' : 'badge-gold' }}">{{ $migration['ran'] ? 'Ran' : 'Pending' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="2" style="text-align:center;padding:32px;color:var(--text-muted);">No migration files found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Table Row Counts</div>
        </div>
        <table>
            <thead><tr><th>Module</th><th>Table</th><th style="text-align:right;">Rows</th></tr></thead>
            <tbody>
            @foreach($tableCounts as $count)
                <tr>
                    <td style="font-size:12px;font-weight:600;color:var(--navy);">{{ $count['label'] }}</td>
                    <td style="font-family:monospace;font-size:11px;color:var(--text-muted);">{{ $count['table'] }}</td>
                    <td style="text-align:right;font-size:12px;color:var(--navy);">{{ $count['rows'] ?? 'N/A' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card" style="padding:20px;">
        <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.1em;">Manual Backup</h3>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">
            Creates a timestamped copy in <code style="font-family:monospace;">storage/backups</code> and records the time against the system settings.
        </p>
        <form method="POST" action="{{ route('admin.backup') }}" onsubmit="return confirm('Create a database backup now?')">
            @csrf
            <button type="submit" class="btn-gold">Create Backup</button>
        </form>
    </div>
</div>
@endsection
