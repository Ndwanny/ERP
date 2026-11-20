@extends('layouts.app')
@section('title', 'Suppliers')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Accounting</div><h1 class="page-title">🏪 Suppliers</h1></div>
        <a href="{{ route('accounting.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Add Supplier</div>
        <form method="POST" action="{{ route('accounting.suppliers.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Contact Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. John Banda"></div>
            <div class="form-group"><label class="form-label">Company</label><input type="text" name="company" class="form-input" value="{{ old('company') }}" placeholder="e.g. Fabric House Ltd"></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="{{ old('email') }}"></div>
            <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone') }}"></div>
            <div class="form-group"><label class="form-label">TPIN</label><input type="text" name="tpin" class="form-input" value="{{ old('tpin') }}"></div>
            <div class="form-group"><label class="form-label">Category</label>
                <select name="category" class="form-input"><option value="">Select…</option>@foreach(['fabric','hardware','timber','packaging','equipment','services','other'] as $c)<option value="{{ $c }}" {{ old('category')==$c?'selected':'' }}>{{ ucfirst($c) }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Address</label><input type="text" name="address" class="form-input" value="{{ old('address') }}"></div>
            <button type="submit" class="btn-gold" style="width:100%;">✓ Add Supplier</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Suppliers</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $suppliers->total() }} total</div>
        </div>
        @forelse($suppliers as $sup)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div>
                <div style="font-weight:600;color:var(--navy);">{{ $sup->name }}@if($sup->company) <span style="font-weight:400;color:var(--text-muted);">· {{ $sup->company }}</span>@endif</div>
                <div style="font-size:12px;color:var(--text-muted);">
                    @if($sup->phone)📞 {{ $sup->phone }} @endif
                    @if($sup->email)· {{ $sup->email }}@endif
                    @if($sup->category) · {{ ucfirst($sup->category) }}@endif
                </div>
                @if($sup->tpin)<div style="font-size:11px;color:var(--text-light);">TPIN: {{ $sup->tpin }}</div>@endif
            </div>
            <div style="text-align:right;">
                <span class="badge badge-muted">{{ $sup->expenses_count }} expenses</span>
                @if($sup->outstanding_balance > 0)
                <div style="font-size:12px;font-weight:600;color:var(--error);margin-top:4px;">Owes ZMW {{ number_format($sup->outstanding_balance,2) }}</div>
                @endif
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No suppliers added yet</div>
        @endforelse
        @if($suppliers->hasPages())
        <div style="padding:16px 20px;">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>
@endsection
