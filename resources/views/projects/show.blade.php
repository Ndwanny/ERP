@extends('layouts.app')
@section('title', $project->project_number)
@section('content')
@php
$statusColors=['planning'=>'badge-muted','active'=>'badge-success','on_hold'=>'badge-gold','completed'=>'badge-navy','cancelled'=>'badge-error'];
@endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">Projects</div>
            <h1 class="page-title">📐 {{ $project->project_number }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                <span class="badge {{ $statusColors[$project->status]??'badge-muted' }}">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
                <span class="badge badge-muted">{{ ucfirst(str_replace('_',' ',$project->type)) }}</span>
                <span style="font-size:13px;color:var(--text-muted);">{{ $project->name }}</span>
            </div>
        </div>
        <a href="{{ route('projects.index') }}" class="btn-outline">← All Projects</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    {{-- Left: Info + Edit + Contractor + Visit --}}
    <div>
        {{-- Progress --}}
        <div class="card" style="padding:20px;margin-bottom:16px;text-align:center;">
            <div style="font-size:42px;font-weight:700;color:var(--navy);">{{ $project->completion_percent }}%</div>
            <div style="margin:12px 0;background:var(--border);border-radius:6px;height:8px;">
                <div style="background:var(--gold);width:{{ $project->completion_percent }}%;height:8px;border-radius:6px;"></div>
            </div>
            <div style="font-size:12px;color:var(--text-muted);">Project Completion</div>
        </div>

        {{-- Key info --}}
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Details</div>
            @php $rows = [
                'Client' => $project->client?->name??'—',
                'Manager' => $project->manager?->name??'—',
                'Location' => $project->location??'—',
                'Start Date' => optional($project->start_date)->format('d M Y')??'—',
                'End Date' => optional($project->end_date)->format('d M Y')??'—',
                'Budget' => $project->budget ? 'ZMW '.number_format($project->budget,2) : '—',
                'Actual Cost' => $project->actual_cost ? 'ZMW '.number_format($project->actual_cost,2) : '—',
            ]; @endphp
            @foreach($rows as $l=>$v)
            <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                <span style="color:var(--text-muted);width:90px;flex-shrink:0;">{{ $l }}</span>
                <span>{{ $v }}</span>
            </div>
            @endforeach
            @if($project->description)<p style="font-size:12px;color:var(--text-muted);margin-top:12px;">{{ $project->description }}</p>@endif
        </div>

        {{-- Update form --}}
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Update Project</div>
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Status</label>
                    <select name="status" class="form-input">@foreach(['planning'=>'Planning','active'=>'Active','on_hold'=>'On Hold','completed'=>'Completed','cancelled'=>'Cancelled'] as $v=>$l)<option value="{{ $v }}" {{ $project->status==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Completion (%)</label><input type="number" name="completion_percent" class="form-input" value="{{ $project->completion_percent }}" min="0" max="100"></div>
                <div class="form-group"><label class="form-label">Actual Cost (ZMW)</label><input type="number" name="actual_cost" class="form-input" value="{{ $project->actual_cost }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-input" value="{{ optional($project->end_date)->format('Y-m-d') }}"></div>
                <div class="form-group"><label class="form-label">Manager *</label>
                    <select name="manager_id" class="form-input" required>@foreach($users as $u)<option value="{{ $u->id }}" {{ $project->manager_id==$u->id?'selected':'' }}>{{ $u->name }}</option>@endforeach</select>
                </div>
                <input type="hidden" name="name" value="{{ $project->name }}">
                <input type="hidden" name="budget" value="{{ $project->budget }}">
                <input type="hidden" name="description" value="{{ $project->description }}">
                <input type="hidden" name="location" value="{{ $project->location }}">
                <button type="submit" class="btn-navy" style="width:100%;font-size:12px;">Save Changes</button>
            </form>
        </div>

        {{-- Contractor form --}}
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Assign Contractor</div>
            <form method="POST" action="{{ route('projects.contractors.assign', $project) }}">
                @csrf
                <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" required placeholder="Contractor name"></div>
                <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" class="form-input" placeholder="Company name"></div>
                <div class="form-group"><label class="form-label">Trade *</label><input type="text" name="trade" class="form-input" required placeholder="e.g. Electrician"></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" placeholder="+260 9xx…"></div>
                <div class="form-group"><label class="form-label">Contract Value (ZMW)</label><input type="number" name="contract_value" class="form-input" min="0" step="0.01" placeholder="0.00"></div>
                <button type="submit" class="btn-outline" style="width:100%;font-size:12px;">+ Assign</button>
            </form>
        </div>

        {{-- Site visit form --}}
        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Log Site Visit</div>
            <form method="POST" action="{{ route('projects.site-visits.store', $project) }}">
                @csrf
                <div class="form-group"><label class="form-label">Visit Date *</label><input type="date" name="visit_date" class="form-input" value="{{ date('Y-m-d') }}" required></div>
                <div class="form-group"><label class="form-label">Observations *</label><textarea name="observations" class="form-input" rows="3" required placeholder="What was observed / done on site…"></textarea></div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-input" required>@foreach(['on_track'=>'On Track','delayed'=>'Delayed','issue'=>'Issue'] as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Photos</label><input type="text" name="photos" class="form-input" placeholder="Comma-separated file paths"></div>
                <button type="submit" class="btn-outline" style="width:100%;font-size:12px;">📍 Log Visit</button>
            </form>
        </div>
    </div>

    {{-- Right: Milestones + Contractors + Site Visits --}}
    <div>
        {{-- Milestones --}}
        <div class="card" style="margin-bottom:16px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Milestones</div>
                <button onclick="document.getElementById('add-milestone').style.display='block';this.style.display='none';" class="btn-outline" style="font-size:11px;padding:6px 12px;">+ Add</button>
            </div>
            <div id="add-milestone" style="display:none;padding:16px 20px;border-bottom:1px solid var(--border);">
                <form method="POST" action="{{ route('projects.milestones.store', $project) }}" style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;align-items:end;">
                    @csrf
                    <div class="form-group" style="margin:0;"><label class="form-label">Title *</label><input type="text" name="title" class="form-input" required placeholder="Milestone title"></div>
                    <div class="form-group" style="margin:0;"><label class="form-label">Due Date *</label><input type="date" name="due_date" class="form-input" required></div>
                    <button type="submit" class="btn-gold">Add</button>
                </form>
            </div>
            @php $msColors=['pending'=>'badge-muted','in_progress'=>'badge-gold','completed'=>'badge-success']; @endphp
            @forelse($project->milestones as $ms)
            <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    @if($ms->status==='completed')
                    <span style="color:var(--success);font-size:16px;">✓</span>
                    @else
                    <span style="color:var(--border);font-size:16px;">○</span>
                    @endif
                    <div>
                        <div style="font-weight:500;color:{{ $ms->status==='completed'?'var(--text-muted)':'var(--navy)' }};{{ $ms->status==='completed'?'text-decoration:line-through;':'' }}">{{ $ms->title }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">Due: {{ $ms->due_date->format('d M Y') }}@if($ms->completed_date) · Done: {{ $ms->completed_date->format('d M Y') }}@endif</div>
                    </div>
                </div>
                @if($ms->status !== 'completed')
                <form method="POST" action="{{ route('projects.milestones.update', $ms) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-outline" style="font-size:11px;padding:5px 10px;">Mark Done</button>
                </form>
                @else
                <span class="badge badge-success" style="font-size:10px;">Done</span>
                @endif
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No milestones yet. Add the first one above.</div>
            @endforelse
        </div>

        {{-- Contractors --}}
        @if($project->contractors->count())
        <div class="card" style="margin-bottom:16px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Contractors ({{ $project->contractors->count() }})</div>
            </div>
            @foreach($project->contractors as $con)
            <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <div>
                    <div style="font-weight:500;color:var(--navy);">{{ $con->name }}</div>
                    <div style="font-size:12px;color:var(--text-muted);">
                        {{ $con->trade }}
                        @if($con->company) · {{ $con->company }}@endif
                        @if($con->phone) · {{ $con->phone }}@endif
                    </div>
                </div>
                @if($con->contract_value)<div style="font-size:12px;color:var(--text-muted);">ZMW {{ number_format($con->contract_value,2) }}</div>@endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Site Visits --}}
        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Site Visits ({{ $project->siteVisits->count() }})</div>
            </div>
            @php $visitColors=['on_track'=>'badge-success','delayed'=>'badge-gold','issue'=>'badge-error']; @endphp
            @forelse($project->siteVisits as $visit)
            <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);font-size:13px;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                    <div style="font-weight:500;color:var(--navy);">📍 {{ $visit->visit_date->format('d M Y') }} <span class="badge {{ $visitColors[$visit->status]??'badge-muted' }}" style="font-size:10px;">{{ ucfirst(str_replace('_',' ',$visit->status)) }}</span></div>
                    @if($visit->user)<div style="font-size:12px;color:var(--text-muted);">{{ $visit->user?->name??'—' }}</div>@endif
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px;white-space:pre-line;">{{ $visit->observations }}</div>
                @if($visit->photos)<div style="font-size:11px;color:var(--text-light);margin-top:4px;">📷 {{ implode(', ', $visit->photos) }}</div>@endif
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No site visits logged yet</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
