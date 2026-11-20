@extends('layouts.app')
@section('title', 'Edit Client')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">CRM</div><h1 class="page-title">✏️ Edit {{ $client->name }}</h1></div>
        <a href="{{ route('crm.clients.show', $client) }}" class="btn-outline">← Back</a>
    </div>
</div>
<div class="page-body">
    <form method="POST" action="{{ route('crm.clients.update', $client) }}" style="max-width:800px;">
        @csrf @method('PUT')
        @if($errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif
        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Client Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" value="{{ old('name', $client->name) }}" required></div>
                <div class="form-group"><label class="form-label">Type *</label>
                    <select name="type" class="form-input" required>@foreach(['institutional'=>'Institutional','corporate'=>'Corporate','individual'=>'Individual','retail'=>'Retail'] as $v=>$l)<option value="{{ $v }}" {{ old('type',$client->type)==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" class="form-input" value="{{ old('company', $client->company) }}"></div>
                <div class="form-group"><label class="form-label">Industry</label><input type="text" name="industry" class="form-input" value="{{ old('industry', $client->industry) }}"></div>
                <div class="form-group"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-input" value="{{ old('contact_person', $client->contact_person) }}"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="{{ old('email', $client->email) }}"></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone', $client->phone) }}"></div>
                <div class="form-group"><label class="form-label">WhatsApp</label><input type="text" name="whatsapp" class="form-input" value="{{ old('whatsapp', $client->whatsapp) }}"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Address</label><textarea name="address" class="form-input" rows="2">{{ old('address', $client->address) }}</textarea></div>
                <div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-input" value="{{ old('city', $client->city) }}"></div>
                <div class="form-group"><label class="form-label">Country</label><input type="text" name="country" class="form-input" value="{{ old('country', $client->country) }}"></div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-input" required>@foreach(['lead'=>'Lead','prospect'=>'Prospect','active'=>'Active','dormant'=>'Dormant','lost'=>'Lost'] as $v=>$l)<option value="{{ $v }}" {{ old('status',$client->status)==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Tier *</label>
                    <select name="tier" class="form-input" required>@foreach(['standard'=>'Standard','vip'=>'VIP','enterprise'=>'Enterprise'] as $v=>$l)<option value="{{ $v }}" {{ old('tier',$client->tier)==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Assigned To</label>
                    <select name="assigned_to" class="form-input"><option value="">Unassigned</option>@foreach($users as $u)<option value="{{ $u->id }}" {{ old('assigned_to',$client->assigned_to)==$u->id?'selected':'' }}>{{ $u->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Lifetime Value (ZMW)</label><input type="number" name="lifetime_value" class="form-input" value="{{ old('lifetime_value', $client->lifetime_value) }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Referral Source</label><input type="text" name="referral_source" class="form-input" value="{{ old('referral_source', $client->referral_source) }}"></div>
                <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="3">{{ old('notes', $client->notes) }}</textarea></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Save Changes</button>
            <a href="{{ route('crm.clients.show', $client) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
