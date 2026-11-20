@extends('layouts.app')
@section('title', $client->name)
@section('content')
@php
$statusColors=['lead'=>'badge-gold','prospect'=>'badge-navy','active'=>'badge-success','dormant'=>'badge-muted','lost'=>'badge-error'];
$interactionIcons=['call'=>'📞','email'=>'✉️','meeting'=>'🤝','whatsapp'=>'💬','site_visit'=>'📍','note'=>'📝'];
@endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">CRM / Clients</div>
            <h1 class="page-title">🤝 {{ $client->name }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                <span class="badge {{ $statusColors[$client->status]??'badge-muted' }}">{{ ucfirst($client->status) }}</span>
                <span class="badge badge-muted">{{ ucfirst($client->type) }}</span>
                <span class="badge badge-gold">{{ ucfirst($client->tier) }}</span>
            </div>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('crm.index') }}" class="btn-outline">← All Clients</a>
            <a href="{{ route('crm.clients.edit', $client) }}" class="btn-outline">Edit</a>
            <form method="POST" action="{{ route('crm.clients.destroy', $client) }}" onsubmit="return confirm('Delete this client?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-outline" style="color:var(--error);border-color:var(--error);">Delete</button>
            </form>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
        <div>
            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Contact Details</div>
                @php $rows = [
                    'Company' => $client->company ?: '—',
                    'Contact' => $client->contact_person ?: '—',
                    'Industry' => $client->industry ?: '—',
                    'Email' => $client->email ?: '—',
                    'Phone' => $client->phone ?: '—',
                    'WhatsApp' => $client->whatsapp ?: '—',
                    'Address' => $client->address ?: '—',
                    'City' => $client->city ?: '—',
                    'Country' => $client->country ?: '—',
                    'Assigned To' => $client->assignedTo?->name ?? '—',
                    'Referral' => $client->referral_source ?: '—',
                ]; @endphp
                @foreach($rows as $label => $value)
                <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-light);font-size:12px;">
                    <span style="color:var(--text-muted);width:82px;flex-shrink:0;">{{ $label }}</span>
                    <span>{{ $value }}</span>
                </div>
                @endforeach
            </div>

            <div class="card" style="padding:20px;margin-bottom:16px;text-align:center;">
                <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Lifetime Value</div>
                <div style="font-size:26px;font-weight:700;color:var(--navy);">ZMW {{ number_format($client->lifetime_value, 2) }}</div>
            </div>

            @if($client->notes)
            <div class="card" style="padding:20px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.08em;">Notes</div>
                <div style="font-size:12px;color:var(--text-muted);white-space:pre-line;">{{ $client->notes }}</div>
            </div>
            @endif
        </div>

        <div>
            <div class="card" style="margin-bottom:16px;">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                    <div style="font-size:13px;font-weight:600;color:var(--navy);">Interactions ({{ $client->interactions->count() }})</div>
                </div>
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                    <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Log Interaction</div>
                    <form method="POST" action="{{ route('crm.interactions.store') }}">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <div style="display:grid;grid-template-columns:1fr 2fr;gap:12px;">
                            <div class="form-group" style="margin:0;"><label class="form-label">Type *</label>
                                <select name="type" class="form-input" required>@foreach($interactionIcons as $v=>$icon)<option value="{{ $v }}">{{ $icon }} {{ ucfirst(str_replace('_',' ',$v)) }}</option>@endforeach</select>
                            </div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Subject *</label><input type="text" name="subject" class="form-input" required placeholder="Brief subject"></div>
                            <div class="form-group" style="margin:0;grid-column:span 2;"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="2" placeholder="What happened…"></textarea></div>
                            <div class="form-group" style="margin:0;"><label class="form-label">Follow-up Date</label><input type="date" name="follow_up_date" class="form-input"></div>
                            <div style="display:flex;align-items:end;"><button type="submit" class="btn-gold">+ Log</button></div>
                        </div>
                    </form>
                </div>
                @forelse($client->interactions as $interaction)
                <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);font-size:13px;">
                    <div style="display:flex;justify-content:space-between;gap:8px;">
                        <div style="font-weight:600;color:var(--navy);">{{ $interactionIcons[$interaction->type]??'📝' }} {{ $interaction->subject }}</div>
                        <div style="font-size:11px;color:var(--text-muted);white-space:nowrap;">{{ $interaction->created_at->format('d M Y') }}</div>
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ ucfirst(str_replace('_',' ',$interaction->type)) }} by {{ $interaction->user?->name ?? '—' }}</div>
                    @if($interaction->notes)<div style="font-size:12px;color:var(--text-muted);margin-top:6px;white-space:pre-line;">{{ $interaction->notes }}</div>@endif
                    @if($interaction->follow_up_date)<div style="font-size:11px;color:var(--gold);margin-top:4px;">Follow-up: {{ $interaction->follow_up_date->format('d M Y') }}</div>@endif
                </div>
                @empty
                <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No interactions logged yet</div>
                @endforelse
            </div>

            <div class="card">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                    <div style="font-size:13px;font-weight:600;color:var(--navy);">Projects ({{ $client->projects->count() }})</div>
                </div>
                @forelse($client->projects as $project)
                <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                    <div>
                        <a href="{{ route('projects.show', $project) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $project->project_number }}</a>
                        <div style="font-size:12px;color:var(--text-muted);">{{ $project->name }}</div>
                    </div>
                    <span class="badge badge-muted">{{ $project->completion_percent }}%</span>
                </div>
                @empty
                <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No projects for this client yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
