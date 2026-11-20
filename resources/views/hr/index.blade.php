@extends('layouts.app')
@section('title', 'HR & Payroll')
@section('content')
<div class="page-header"><div style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:rgba(201,168,76,0.5);margin-bottom:6px;">Module 08</div><h1 class="page-title">👔 HR & Payroll</h1></div>
<div class="page-body">
@if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
    <a href="{{ route('hr.employees') }}" class="card" style="padding:20px;text-decoration:none;display:block;"><div style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(201,168,76,0.5);">Active Staff</div><div style="font-size:24px;margin-top:8px;">👤</div><div style="font-size:22px;font-weight:700;color:var(--navy);margin-top:6px;">{{ $staff_count }}</div></a>
    <a href="{{ route('hr.attendance') }}" class="card" style="padding:20px;text-decoration:none;display:block;"><div style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(201,168,76,0.5);">On Duty Today</div><div style="font-size:24px;margin-top:8px;">🕐</div><div style="font-size:22px;font-weight:700;color:var(--navy);margin-top:6px;">{{ $on_duty_today }}</div></a>
    <a href="{{ route('hr.leave') }}" class="card" style="padding:20px;text-decoration:none;display:block;"><div style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(201,168,76,0.5);">Pending Leave</div><div style="font-size:24px;margin-top:8px;">🌴</div><div style="font-size:22px;font-weight:700;color:var(--navy);margin-top:6px;">{{ $pending_leave }}</div></a>
    <a href="{{ route('hr.payroll') }}" class="card" style="padding:20px;text-decoration:none;display:block;"><div style="font-size:10px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(201,168,76,0.5);">Latest Payroll</div><div style="font-size:24px;margin-top:8px;">💵</div><div style="font-size:18px;font-weight:700;color:var(--navy);margin-top:6px;">{{ $latest_payroll?->period ?? '—' }}</div><div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $latest_payroll ? 'Net ZMW '.number_format($latest_payroll->total_net,2) : 'No runs yet' }}</div></a>
</div>
</div>
@endsection
