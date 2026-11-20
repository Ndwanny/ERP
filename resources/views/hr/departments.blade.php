@extends('layouts.app')
@section('title', 'Departments')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">HR & Payroll</div><h1 class="page-title">🏢 Departments</h1></div>
        <a href="{{ route('hr.employees') }}" class="btn-outline">← Employees</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Add Department</div>
        <form method="POST" action="{{ route('hr.departments.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Department Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. Production, Sales"></div>
            @if($errors->any())<div class="alert alert-error" style="margin-bottom:12px;">{{ $errors->first() }}</div>@endif
            <button type="submit" class="btn-gold" style="width:100%;">+ Add Department</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">All Departments</div>
        </div>
        @forelse($departments as $dept)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-weight:600;color:var(--navy);">{{ $dept->name }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <span class="badge badge-navy">{{ $dept->employees_count }} staff</span>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No departments yet. Add one using the form.</div>
        @endforelse
    </div>
</div>
@endsection
