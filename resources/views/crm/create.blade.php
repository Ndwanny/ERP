@extends('layouts.app')
@section('title', 'New Client')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">CRM</div><h1 class="page-title">🤝 New Client</h1></div>
        <a href="{{ route('crm.index') }}" class="btn-outline">← Back</a>
    </div>
</div>
<div class="page-body">
    <form method="POST" action="{{ route('crm.clients.store') }}" style="max-width:800px;">
        @csrf
        @if($errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif
        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Client Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="Client or contact name"></div>
                <div class="form-group"><label class="form-label">Type *</label>
                    <select name="type" class="form-input" required>@foreach(['institutional'=>'Institutional','corporate'=>'Corporate','individual'=>'Individual','retail'=>'Retail'] as $v=>$l)<option value="{{ $v }}" {{ old('type','corporate')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" class="form-input" value="{{ old('company') }}" placeholder="Company name"></div>
                <div class="form-group"><label class="form-label">Industry</label><input type="text" name="industry" class="form-input" value="{{ old('industry') }}" placeholder="e.g. Banking"></div>
                <div class="form-group"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-input" value="{{ old('contact_person') }}" placeholder="Primary contact"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="name@company.com"></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+260 9xx…"></div>
                <div class="form-group"><label class="form-label">WhatsApp</label><input type="text" name="whatsapp" class="form-input" value="{{ old('whatsapp') }}" placeholder="+260 9xx…"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Address</label><textarea name="address" class="form-input" rows="2" placeholder="Physical address">{{ old('address') }}</textarea></div>
                <div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-input" value="{{ old('city','Lusaka') }}"></div>
                <div class="form-group"><label class="form-label">Country</label><input type="text" name="country" class="form-input" value="{{ old('country','Zambia') }}"></div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-input" required>@foreach(['lead'=>'Lead','prospect'=>'Prospect','active'=>'Active','dormant'=>'Dormant','lost'=>'Lost'] as $v=>$l)<option value="{{ $v }}" {{ old('status','lead')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Tier *</label>
                    <select name="tier" class="form-input" required>@foreach(['standard'=>'Standard','vip'=>'VIP','enterprise'=>'Enterprise'] as $v=>$l)<option value="{{ $v }}" {{ old('tier','standard')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Assigned To</label>
                    <select name="assigned_to" class="form-input"><option value="">Unassigned</option>@foreach($users as $u)<option value="{{ $u->id }}" {{ old('assigned_to')==$u->id?'selected':'' }}>{{ $u->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Lifetime Value (ZMW)</label><input type="number" name="lifetime_value" class="form-input" value="{{ old('lifetime_value',0) }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Referral Source</label><input type="text" name="referral_source" class="form-input" value="{{ old('referral_source') }}" placeholder="e.g. Website, referral"></div>
                <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="3" placeholder="Internal notes…">{{ old('notes') }}</textarea></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Create Client</button>
            <a href="{{ route('crm.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
