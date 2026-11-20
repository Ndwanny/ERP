@extends('layouts.app')
@section('title', 'Attendance Register')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">HR & Payroll</div><h1 class="page-title">🕐 Attendance Register</h1></div>
        <a href="{{ route('hr.employees') }}" class="btn-outline">← Employees</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;">

    <div>
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Record Attendance</div>
            <form method="POST" action="{{ route('hr.attendance.check-in') }}">
                @csrf
                <div class="form-group"><label class="form-label">Employee *</label>
                    <select name="employee_id" class="form-input" required>
                        <option value="">Select employee…</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Date *</label><input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}" required></div>
                <div class="form-group"><label class="form-label">Check-In Time *</label><input type="time" name="check_in" class="form-input" value="{{ date('H:i') }}" required></div>
                <div class="form-group"><label class="form-label">Status *</label>
                    <select name="status" class="form-input" required>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="half_day">Half Day</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Notes</label><input type="text" name="notes" class="form-input" placeholder="Optional note"></div>
                <button type="submit" class="btn-gold" style="width:100%;">✓ Record Check-In</button>
            </form>
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Record Check-Out</div>
            <form method="POST" action="{{ route('hr.attendance.check-out') }}">
                @csrf
                <div class="form-group"><label class="form-label">Employee *</label>
                    <select name="employee_id" class="form-input" required>
                        <option value="">Select employee…</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Date *</label><input type="date" name="date" class="form-input" value="{{ date('Y-m-d') }}" required></div>
                <div class="form-group"><label class="form-label">Check-Out Time *</label><input type="time" name="check_out" class="form-input" value="{{ date('H:i') }}" required></div>
                <button type="submit" class="btn-navy" style="width:100%;font-size:12px;">✓ Record Check-Out</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Attendance Log</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $records->total() }} records</div>
        </div>
        @php $atColors=['present'=>'badge-success','late'=>'badge-gold','half_day'=>'badge-navy','absent'=>'badge-error']; @endphp
        @forelse($records as $rec)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:grid;grid-template-columns:2fr 1fr 1fr 1fr;align-items:center;gap:8px;font-size:13px;">
            <div>
                <a href="{{ route('hr.employees.show', $rec->employee) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $rec->employee->first_name }} {{ $rec->employee->last_name }}</a>
                <div style="font-size:11px;color:var(--text-muted);">{{ $rec->employee->employee_number }}</div>
            </div>
            <div>
                <span class="badge {{ $atColors[$rec->status]??'badge-muted' }}" style="font-size:10px;">{{ ucfirst($rec->status) }}</span>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}</div>
            </div>
            <div style="color:var(--text-muted);font-size:12px;">
                In: {{ $rec->check_in??'—' }}<br>Out: {{ $rec->check_out??'—' }}
            </div>
            <div style="text-align:right;font-size:12px;color:var(--text-muted);">
                @if($rec->hours_worked)<strong style="color:var(--navy);">{{ $rec->hours_worked }}h</strong>@else—@endif
                @if($rec->notes)<div style="font-size:11px;">{{ $rec->notes }}</div>@endif
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No attendance records yet</div>
        @endforelse
        @if($records->hasPages())
        <div style="padding:16px 20px;">{{ $records->links() }}</div>
        @endif
    </div>
</div>
@endsection
