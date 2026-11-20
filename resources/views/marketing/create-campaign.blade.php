@extends('layouts.app')
@section('title', 'New Campaign')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Marketing</div><h1 class="page-title">📢 New Campaign</h1></div>
        <a href="{{ route('marketing.campaigns') }}" class="btn-outline">← Back</a>
    </div>
</div>
<div class="page-body">
    <form method="POST" action="{{ route('marketing.campaigns.store') }}" style="max-width:600px;">
        @csrf
        @if($errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif
        <div class="card" style="padding:24px;margin-bottom:20px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Campaign Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. End of Year Sale 2026"></div>
                <div class="form-group"><label class="form-label">Type *</label>
                    <select name="type" class="form-input" required>@foreach(['email'=>'Email','social_media'=>'Social Media','event'=>'Event','corporate_outreach'=>'Corporate Outreach','whatsapp'=>'WhatsApp','print'=>'Print'] as $v=>$l)<option value="{{ $v }}" {{ old('type')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-input" required>@foreach(['draft'=>'Draft','scheduled'=>'Scheduled','active'=>'Active','paused'=>'Paused','completed'=>'Completed'] as $v=>$l)<option value="{{ $v }}" {{ old('status','draft')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Start Date *</label><input type="date" name="start_date" class="form-input" value="{{ old('start_date', date('Y-m-d')) }}" required></div>
                <div class="form-group"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-input" value="{{ old('end_date') }}"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Budget (ZMW)</label><input type="number" name="budget" class="form-input" value="{{ old('budget') }}" min="0" step="0.01" placeholder="0.00"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Description / Goals</label><textarea name="description" class="form-input" rows="3">{{ old('description') }}</textarea></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Create Campaign</button>
            <a href="{{ route('marketing.campaigns') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
