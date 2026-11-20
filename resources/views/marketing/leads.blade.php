@extends('layouts.app')
@section('title', 'Leads')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Marketing</div><h1 class="page-title">🎯 Leads</h1></div>
        <a href="{{ route('marketing.index') }}" class="btn-outline">← Marketing</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Capture Lead</div>
        <form method="POST" action="{{ route('marketing.leads.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="Lead's name"></div>
            <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" class="form-input" value="{{ old('company') }}" placeholder="Company name"></div>
            <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+260 9xx…"></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="{{ old('email') }}"></div>
            <div class="form-group"><label class="form-label">Source *</label>
                <select name="source" class="form-input" required>@foreach(['instagram','facebook','whatsapp','website','referral','walk_in','event','tiktok','other'] as $s)<option value="{{ $s }}" {{ old('source')==$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Campaign</label>
                <select name="campaign_id" class="form-input"><option value="">No campaign</option>@foreach($campaigns as $c)<option value="{{ $c->id }}" {{ old('campaign_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Interest / Inquiry *</label>
                <select name="interest" class="form-input" required>@foreach(['bags'=>'Bags','furniture'=>'Furniture','interiors'=>'Interiors','corporate_bags'=>'Corporate Bags','other'=>'Other'] as $v=>$l)<option value="{{ $v }}" {{ old('interest')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Notes</label><input type="text" name="notes" class="form-input" placeholder="Any additional notes"></div>
            <button type="submit" class="btn-gold" style="width:100%;">✓ Capture Lead</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">All Leads ({{ $leads->total() }})</div>
        </div>
        @php $leadColors=['new'=>'badge-gold','contacted'=>'badge-navy','qualified'=>'badge-success','converted'=>'badge-success','lost'=>'badge-error']; @endphp
        @forelse($leads as $lead)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:start;font-size:13px;">
            <div>
                <div style="font-weight:600;color:var(--navy);">{{ $lead->name }}@if($lead->company) <span style="font-weight:400;color:var(--text-muted);">· {{ $lead->company }}</span>@endif</div>
                <div style="font-size:12px;color:var(--text-muted);">
                    {{ ucwords(str_replace('_',' ',$lead->source)) }}
                    @if($lead->campaign) · {{ $lead->campaign->name }}@endif
                    @if($lead->phone) · {{ $lead->phone }}@endif
                </div>
                @if($lead->interest)<div style="font-size:11px;color:var(--text-light);margin-top:2px;">{{ ucwords(str_replace('_',' ',$lead->interest)) }}</div>@endif
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                <span class="badge {{ $leadColors[$lead->status]??'badge-muted' }}" style="font-size:10px;">{{ ucfirst($lead->status) }}</span>
                <span style="font-size:11px;color:var(--text-muted);">{{ $lead->created_at->format('d M Y') }}</span>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No leads captured yet</div>
        @endforelse
        @if($leads->hasPages())<div style="padding:16px 20px;">{{ $leads->links() }}</div>@endif
    </div>
</div>
@endsection
