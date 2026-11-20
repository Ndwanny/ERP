@extends('layouts.app')
@section('title', 'New Project')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Projects</div><h1 class="page-title">📐 New Project</h1></div>
        <a href="{{ route('projects.index') }}" class="btn-outline">← Back</a>
    </div>
</div>
<div class="page-body">
    <form method="POST" action="{{ route('projects.store') }}" style="max-width:700px;">
        @csrf
        @if($errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Project Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Project Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. ZANACO Branch Interior Fitout"></div>
                <div class="form-group"><label class="form-label">Client *</label>
                    <select name="client_id" class="form-input" required>
                        <option value="">Select client…</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" {{ old('client_id')==$c->id?'selected':'' }}>{{ $c->name }}{{ $c->company?' ('.$c->company.')':'' }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Project Manager *</label>
                    <select name="manager_id" class="form-input" required>
                        <option value="">Select manager…</option>
                        @foreach($users as $u)<option value="{{ $u->id }}" {{ old('manager_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Type *</label>
                    <select name="type" class="form-input" required>@foreach(['workplace'=>'Workplace','hospitality'=>'Hospitality','retail'=>'Retail','residential'=>'Residential','other'=>'Other'] as $v=>$l)<option value="{{ $v }}" {{ old('type')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-input" required>@foreach(['planning'=>'Planning','active'=>'Active','on_hold'=>'On Hold','completed'=>'Completed','cancelled'=>'Cancelled'] as $v=>$l)<option value="{{ $v }}" {{ old('status','planning')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Location / Site</label><input type="text" name="location" class="form-input" value="{{ old('location') }}" placeholder="e.g. Cairo Road, Lusaka CBD"></div>
                <div class="form-group"><label class="form-label">Start Date *</label><input type="date" name="start_date" class="form-input" value="{{ old('start_date', date('Y-m-d')) }}" required></div>
                <div class="form-group"><label class="form-label">End Date (Deadline)</label><input type="date" name="end_date" class="form-input" value="{{ old('end_date') }}"></div>
                <div class="form-group"><label class="form-label">Budget (ZMW)</label><input type="number" name="budget" class="form-input" value="{{ old('budget') }}" min="0" step="0.01" placeholder="0.00"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Description / Scope</label><textarea name="description" class="form-input" rows="3" placeholder="Brief description of project scope…">{{ old('description') }}</textarea></div>
            </div>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Create Project</button>
            <a href="{{ route('projects.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
