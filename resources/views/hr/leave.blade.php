@extends('layouts.app')
@section('title', 'Leave Management')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">HR & Payroll</div><h1 class="page-title">🌴 Leave Management</h1></div>
        <a href="{{ route('hr.employees') }}" class="btn-outline">← Employees</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;">

    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Apply for Leave</div>
        <form method="POST" action="{{ route('hr.leave.apply') }}">
            @csrf
            <div class="form-group"><label class="form-label">Employee *</label>
                <select name="employee_id" class="form-input" required>
                    <option value="">Select employee…</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Leave Type *</label>
                <select name="type" class="form-input" required>
                    @foreach(['annual'=>'Annual Leave','sick'=>'Sick Leave','maternity'=>'Maternity Leave','paternity'=>'Paternity Leave','unpaid'=>'Unpaid Leave','emergency'=>'Emergency Leave'] as $v=>$l)
                    <option value="{{ $v }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Start Date *</label><input type="date" name="start_date" class="form-input" required></div>
            <div class="form-group"><label class="form-label">End Date *</label><input type="date" name="end_date" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Reason</label><textarea name="reason" class="form-input" rows="2" placeholder="Brief reason…"></textarea></div>
            <button type="submit" class="btn-gold" style="width:100%;">Submit Leave Request</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Leave Requests</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $requests->total() }} total</div>
        </div>
        @php $lvColors=['pending'=>'badge-gold','approved'=>'badge-success','rejected'=>'badge-error']; @endphp
        @forelse($requests as $req)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);font-size:13px;">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <a href="{{ route('hr.employees.show', $req->employee) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $req->employee->first_name }} {{ $req->employee->last_name }}</a>
                    <span class="badge {{ $lvColors[$req->status]??'badge-muted' }}" style="font-size:10px;margin-left:8px;">{{ ucfirst($req->status) }}</span>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                        {{ ucfirst($req->type) }} Leave · {{ $req->days_requested }} day{{ $req->days_requested!=1?'s':'' }}
                        · {{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} → {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}
                    </div>
                    @if($req->reason)<div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $req->reason }}</div>@endif
                    @if($req->rejection_reason)<div style="font-size:12px;color:var(--error);margin-top:2px;">Rejected: {{ $req->rejection_reason }}</div>@endif
                </div>
                @if($req->status === 'pending')
                <div style="display:flex;gap:8px;flex-shrink:0;">
                    <form method="POST" action="{{ route('hr.leave.approve', $req) }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="action" value="approved">
                        <button type="submit" class="btn-gold" style="font-size:11px;padding:6px 12px;">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('hr.leave.approve', $req) }}" style="display:inline;" onsubmit="return confirm('Reject this leave?')">
                        @csrf
                        <input type="hidden" name="action" value="rejected">
                        <button type="submit" class="btn-outline" style="font-size:11px;padding:6px 12px;color:var(--error);border-color:var(--error);">Reject</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No leave requests yet</div>
        @endforelse
        @if($requests->hasPages())
        <div style="padding:16px 20px;">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection
