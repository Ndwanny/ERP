@extends('layouts.app')
@section('title', $employee->first_name.' '.$employee->last_name)
@section('content')
@php
$statusColors=['active'=>'badge-success','on_leave'=>'badge-gold','suspended'=>'badge-error','terminated'=>'badge-muted'];
@endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">HR & Payroll</div>
            <h1 class="page-title">👔 {{ $employee->first_name }} {{ $employee->last_name }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;">
                <span class="badge {{ $statusColors[$employee->status]??'badge-muted' }}">{{ ucwords(str_replace('_',' ',$employee->status)) }}</span>
                <span class="badge badge-muted">{{ $employee->employee_number }}</span>
                <span class="badge badge-muted">{{ $employee->job_title }}</span>
            </div>
        </div>
        <a href="{{ route('hr.employees') }}" class="btn-outline">← All Employees</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    <div>
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Employment Info</div>
            @php $rows=[
                'Employee #'=>$employee->employee_number,
                'Type'=>ucwords(str_replace('_',' ',$employee->employment_type)),
                'Department'=>$employee->department?->name??'—',
                'Vertical'=>$employee->vertical?ucfirst($employee->vertical):'—',
                'Salary'=>'ZMW '.number_format($employee->basic_salary,2).' / '.ucfirst($employee->salary_type),
                'Hire Date'=>optional($employee->hire_date)->format('d M Y')??'—',
                'NAPSA'=>$employee->napsa_number??'—',
                'NHIMA'=>$employee->nhima_number??'—',
                'TPIN'=>$employee->tpin??'—',
            ]; @endphp
            @foreach($rows as $l=>$v)
            <div style="display:flex;gap:8px;padding:7px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                <span style="color:var(--text-muted);width:90px;flex-shrink:0;">{{ $l }}</span>
                <span>{{ $v }}</span>
            </div>
            @endforeach
        </div>

        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Contact</div>
            @foreach(['Email'=>$employee->email??'—','Phone'=>$employee->phone??'—','Address'=>$employee->address??'—','Emergency'=>$employee->emergency_contact??'—','Bank'=>$employee->bank_name??'—','Account'=>$employee->bank_account??'—','Mobile Money'=>$employee->mobile_money_number??'—'] as $l=>$v)
            <div style="padding:6px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                <div style="font-size:11px;color:var(--text-muted);">{{ $l }}</div>
                <div>{{ $v }}</div>
            </div>
            @endforeach
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Update Employee</div>
            <form method="POST" action="{{ route('hr.employees.update', $employee) }}">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-input" value="{{ $employee->first_name }}" required></div>
                <div class="form-group"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-input" value="{{ $employee->last_name }}" required></div>
                <div class="form-group"><label class="form-label">Job Title</label><input type="text" name="job_title" class="form-input" value="{{ old('job_title', $employee->job_title) }}" required></div>
                <div class="form-group"><label class="form-label">Department</label>
                    <select name="department_id" class="form-input" required>@foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id', $employee->department_id)==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Basic Salary (ZMW)</label><input type="number" name="basic_salary" class="form-input" value="{{ $employee->basic_salary }}" min="0" step="0.01" required></div>
                <div class="form-group"><label class="form-label">Salary Type</label>
                    <select name="salary_type" class="form-input">@foreach(['monthly'=>'Monthly','daily'=>'Daily','piece_rate'=>'Piece Rate'] as $v=>$l)<option value="{{ $v }}" {{ $employee->salary_type==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Status</label>
                    <select name="status" class="form-input">@foreach(['active'=>'Active','on_leave'=>'On Leave','suspended'=>'Suspended','terminated'=>'Terminated'] as $v=>$l)<option value="{{ $v }}" {{ $employee->status==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ $employee->phone }}"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="{{ $employee->email }}"></div>
                <div class="form-group"><label class="form-label">Employment Type</label>
                    <select name="employment_type" class="form-input">@foreach(['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)<option value="{{ $v }}" {{ $employee->employment_type==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-input" value="{{ optional($employee->hire_date)->format('Y-m-d') }}" required></div>
                <input type="hidden" name="address" value="{{ $employee->address }}">
                <input type="hidden" name="emergency_contact" value="{{ $employee->emergency_contact }}">
                <input type="hidden" name="bank_name" value="{{ $employee->bank_name }}">
                <input type="hidden" name="bank_account" value="{{ $employee->bank_account }}">
                <input type="hidden" name="mobile_money_number" value="{{ $employee->mobile_money_number }}">
                <button type="submit" class="btn-navy" style="width:100%;font-size:12px;">Save Changes</button>
            </form>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Attendance — Last 20 Records</div>
            </div>
            @php $atColors=['present'=>'badge-success','late'=>'badge-gold','half_day'=>'badge-navy','absent'=>'badge-error']; @endphp
            @forelse($employee->attendance as $a)
            <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <div>
                    <span class="badge {{ $atColors[$a->status]??'badge-muted' }}" style="font-size:10px;">{{ ucfirst($a->status) }}</span>
                    <span style="margin-left:8px;">{{ \Carbon\Carbon::parse($a->date)->format('D d M Y') }}</span>
                </div>
                <div style="font-size:12px;color:var(--text-muted);">
                    {{ $a->check_in??'—' }} → {{ $a->check_out??'—' }}
                    @if($a->hours_worked) · {{ $a->hours_worked }}h @endif
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No attendance records</div>
            @endforelse
        </div>

        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Leave Requests</div>
            </div>
            @php $lvColors=['pending'=>'badge-gold','approved'=>'badge-success','rejected'=>'badge-error']; @endphp
            @forelse($employee->leaveRequests as $lv)
            <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);font-size:13px;">
                <div style="display:flex;justify-content:space-between;">
                    <div>
                        <span style="font-weight:600;">{{ ucfirst($lv->type) }} Leave</span>
                        <span class="badge {{ $lvColors[$lv->status]??'badge-muted' }}" style="font-size:10px;margin-left:8px;">{{ ucfirst($lv->status) }}</span>
                    </div>
                    <div style="color:var(--text-muted);">{{ $lv->days_requested }} day{{ $lv->days_requested!=1?'s':'' }}</div>
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                    {{ \Carbon\Carbon::parse($lv->start_date)->format('d M') }} → {{ \Carbon\Carbon::parse($lv->end_date)->format('d M Y') }}
                    @if($lv->reason) · {{ $lv->reason }}@endif
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No leave requests</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
