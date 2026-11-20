@extends('layouts.app')
@section('title', 'System Settings')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Admin</div><h1 class="page-title">⚙️ System Settings</h1></div>
        <a href="{{ route('admin.index') }}" class="btn-outline">← Admin</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif

<div class="page-body">
    <form method="POST" action="{{ route('admin.system.update') }}" style="max-width:700px;">
        @csrf @method('PUT')
        @if($errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Business Information</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Business Name</label><input type="text" name="business_name" class="form-input" value="{{ $settings['business_name']??'My Perfect Stitch' }}"></div>
                <div class="form-group"><label class="form-label">Business Email</label><input type="email" name="business_email" class="form-input" value="{{ $settings['business_email']??'' }}"></div>
                <div class="form-group"><label class="form-label">Business Phone</label><input type="text" name="business_phone" class="form-input" value="{{ $settings['business_phone']??'' }}"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Business Address</label><input type="text" name="business_address" class="form-input" value="{{ $settings['business_address']??'Lusaka, Zambia' }}"></div>
                <div class="form-group"><label class="form-label">TPIN</label><input type="text" name="tpin" class="form-input" value="{{ $settings['tpin']??'' }}" placeholder="ZRA Taxpayer ID"></div>
                <div class="form-group"><label class="form-label">Currency</label>
                    <select name="currency" class="form-input">@foreach(['ZMW'=>'ZMW — Zambian Kwacha','USD'=>'USD — US Dollar','ZAR'=>'ZAR — South African Rand'] as $v=>$l)<option value="{{ $v }}" {{ ($settings['currency']??'ZMW')===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
            </div>
        </div>

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Invoicing Defaults</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Default VAT (%)</label><input type="number" name="default_vat" class="form-input" value="{{ $settings['default_vat']??'16' }}" min="0" max="100" step="0.1"></div>
                <div class="form-group"><label class="form-label">Default Payment Terms (days)</label><input type="number" name="default_payment_terms" class="form-input" value="{{ $settings['default_payment_terms']??'30' }}" min="0"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Default Invoice Terms</label><textarea name="invoice_terms" class="form-input" rows="3">{{ $settings['invoice_terms']??'Payment due within 30 days of invoice date. Late payments subject to interest.' }}</textarea></div>
            </div>
        </div>

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Application Settings</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">App Name</label><input type="text" name="app_name" class="form-input" value="{{ $settings['app_name']??'MPS Suite' }}"></div>
                <div class="form-group"><label class="form-label">Timezone</label>
                    <select name="timezone" class="form-input">@foreach(['Africa/Lusaka'=>'Africa/Lusaka (CAT)','Africa/Johannesburg'=>'Africa/Johannesburg','UTC'=>'UTC'] as $v=>$l)<option value="{{ $v }}" {{ ($settings['timezone']??'Africa/Lusaka')===$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Save Settings</button>
            <a href="{{ route('admin.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
