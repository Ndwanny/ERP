@extends('layouts.app')
@section('title', 'Events')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Marketing</div><h1 class="page-title">🎪 Events</h1></div>
        <a href="{{ route('marketing.index') }}" class="btn-outline">← Marketing</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;">
    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Create Event</div>
        <form method="POST" action="{{ route('marketing.events.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Event Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. Lusaka Trade Fair"></div>
            <div class="form-group"><label class="form-label">Start Date *</label><input type="date" name="start_date" class="form-input" value="{{ old('start_date', date('Y-m-d')) }}" required></div>
            <div class="form-group"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-input" value="{{ old('end_date') }}"></div>
            <div class="form-group"><label class="form-label">Status *</label>
                <select name="status" class="form-input" required>@foreach(['draft'=>'Draft','scheduled'=>'Scheduled','active'=>'Active','paused'=>'Paused','completed'=>'Completed'] as $v=>$l)<option value="{{ $v }}" {{ old('status','draft')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Budget (ZMW)</label><input type="number" name="budget" class="form-input" value="{{ old('budget') }}" min="0" step="0.01" placeholder="0.00"></div>
            <div class="form-group"><label class="form-label">Details</label><textarea name="description" class="form-input" rows="4" placeholder="Venue, audience, goals…">{{ old('description') }}</textarea></div>
            <button type="submit" class="btn-gold" style="width:100%;">✓ Create Event</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Upcoming & Recent Events</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $events->total() }} total</div>
        </div>
        @php $statusColors=['draft'=>'badge-muted','scheduled'=>'badge-navy','active'=>'badge-success','paused'=>'badge-gold','completed'=>'badge-navy']; @endphp
        @forelse($events as $event)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:start;gap:12px;font-size:13px;">
            <div>
                <div style="font-weight:600;color:var(--navy);">{{ $event->name }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                    {{ optional($event->start_date)->format('d M Y') ?? 'Date TBC' }}@if($event->end_date) → {{ $event->end_date->format('d M Y') }}@endif
                    @if($event->budget) · Budget ZMW {{ number_format($event->budget,2) }}@endif
                </div>
                @if($event->description)<div style="font-size:11px;color:var(--text-light);margin-top:4px;">{{ $event->description }}</div>@endif
            </div>
            <span class="badge {{ $statusColors[$event->status]??'badge-muted' }}" style="font-size:10px;flex-shrink:0;">{{ ucfirst($event->status) }}</span>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No events yet. Create one using the form.</div>
        @endforelse
        @if($events->hasPages())<div style="padding:16px 20px;">{{ $events->links() }}</div>@endif
    </div>
</div>
@endsection
