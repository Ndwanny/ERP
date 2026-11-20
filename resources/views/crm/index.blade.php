@extends('layouts.app')
@section('title', 'CRM')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:rgba(201,168,76,0.5);margin-bottom:6px;">Module 04</div><h1 class="page-title">🤝 CRM</h1></div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('crm.pipeline') }}" class="btn-outline">Pipeline</a>
            <a href="{{ route('crm.reports') }}" class="btn-outline">Reports</a>
            <a href="{{ route('crm.clients.create') }}" class="btn-gold">+ New Client</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
        <table>
            <thead><tr><th>Client</th><th>Type</th><th>Contact</th><th>Status</th><th>Tier</th><th>Assigned To</th></tr></thead>
            <tbody>
            @forelse($clients as $client)
                <tr>
                    <td><a href="{{ route('crm.clients.show', $client) }}" style="color:#C9A84C;">{{ $client->name }}</a><br><span style="font-size:11px;color:rgba(245,240,232,0.3);">{{ $client->company }}</span></td>
                    <td>{{ ucfirst($client->type) }}</td>
                    <td>{{ $client->email ?: $client->phone ?: '—' }}</td>
                    <td><span class="badge" style="background:rgba(99,102,241,0.15);color:#818CF8;">{{ ucfirst($client->status) }}</span></td>
                    <td>{{ ucfirst($client->tier) }}</td>
                    <td style="font-size:12px;">{{ $client->assignedTo?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:rgba(245,240,232,0.3);">No clients yet</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($clients->hasPages())<div style="padding:16px 20px;">{{ $clients->links() }}</div>@endif
    </div>
</div>
@endsection
