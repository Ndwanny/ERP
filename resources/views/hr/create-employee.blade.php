@extends('layouts.app')
@section('title', 'New Employee')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">HR & Payroll</div><h1 class="page-title">👔 New Employee</h1></div>
        <a href="{{ route('hr.employees') }}" class="btn-outline">← Back</a>
    </div>
</div>
<div class="page-body">
    <form method="POST" action="{{ route('hr.employees.store') }}" style="max-width:800px;">
        @csrf
        @if($errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Personal Information</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-input" value="{{ old('first_name') }}" required></div>
                <div class="form-group"><label class="form-label">Last Name *</label><input type="text" name="last_name" class="form-input" value="{{ old('last_name') }}" required></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="{{ old('email') }}"></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone') }}"></div>
                <div class="form-group"><label class="form-label">National ID</label><input type="text" name="national_id" class="form-input" value="{{ old('national_id') }}"></div>
                <div class="form-group"><label class="form-label">NAPSA Number</label><input type="text" name="napsa_number" class="form-input" value="{{ old('napsa_number') }}"></div>
                <div class="form-group"><label class="form-label">NHIMA Number</label><input type="text" name="nhima_number" class="form-input" value="{{ old('nhima_number') }}"></div>
                <div class="form-group"><label class="form-label">TPIN</label><input type="text" name="tpin" class="form-input" value="{{ old('tpin') }}"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Residential Address</label><input type="text" name="address" class="form-input" value="{{ old('address') }}"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Emergency Contact</label><input type="text" name="emergency_contact" class="form-input" value="{{ old('emergency_contact') }}" placeholder="Name — relationship — phone"></div>
            </div>
        </div>

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Employment Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Job Title *</label><input type="text" name="job_title" class="form-input" value="{{ old('job_title') }}" required placeholder="e.g. Production Manager"></div>
                <div class="form-group"><label class="form-label">Department</label>
                    <select name="department_id" class="form-input" required><option value="">Select department…</option>@foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Employment Type *</label>
                    <select name="employment_type" class="form-input" required>@foreach(['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)<option value="{{ $v }}" {{ old('employment_type','full_time')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Vertical</label>
                    <select name="vertical" class="form-input"><option value="">All</option>@foreach(['bags','furniture','interiors','admin','shared'] as $v)<option value="{{ $v }}" {{ old('vertical')==$v?'selected':'' }}>{{ ucfirst($v) }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Basic Salary (ZMW) *</label><input type="number" name="basic_salary" class="form-input" value="{{ old('basic_salary') }}" min="0" step="0.01" required></div>
                <div class="form-group"><label class="form-label">Salary Type *</label>
                    <select name="salary_type" class="form-input" required>@foreach(['monthly'=>'Monthly','daily'=>'Daily Rate','piece_rate'=>'Piece Rate'] as $v=>$l)<option value="{{ $v }}" {{ old('salary_type','monthly')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Hire Date *</label><input type="date" name="hire_date" class="form-input" value="{{ old('hire_date', date('Y-m-d')) }}" required></div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-input" required>@foreach(['active'=>'Active','on_leave'=>'On Leave','suspended'=>'Suspended','terminated'=>'Terminated'] as $v=>$l)<option value="{{ $v }}" {{ old('status','active')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
            </div>
        </div>

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Payment Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-input" value="{{ old('bank_name') }}" placeholder="e.g. FNB, Zanaco"></div>
                <div class="form-group"><label class="form-label">Bank Account</label><input type="text" name="bank_account" class="form-input" value="{{ old('bank_account') }}"></div>
                <div class="form-group"><label class="form-label">Mobile Money Number</label><input type="text" name="mobile_money_number" class="form-input" value="{{ old('mobile_money_number') }}" placeholder="+260 9xx xxx xxx"></div>
            </div>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Add Employee</button>
            <a href="{{ route('hr.employees') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
