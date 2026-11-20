@extends('layouts.app')
@section('title', 'Payslips — '.$run->period)
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">HR & Payroll</div>
            <h1 class="page-title">📄 Payslips — {{ $run->period }}</h1>
            <div style="margin-top:6px;font-size:13px;color:var(--text-muted);">Pay date: {{ \Carbon\Carbon::parse($run->pay_date)->format('d M Y') }} · {{ $slips->count() }} employees</div>
        </div>
        <a href="{{ route('hr.payroll') }}" class="btn-outline">← Payroll</a>
    </div>
</div>

<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    {{-- Totals --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px;">
        @foreach(['Total Gross'=>$run->total_gross,'Total PAYE'=>$run->total_paye,'Total NAPSA'=>$run->total_napsa,'Total NHIMA'=>$run->total_nhima,'Total Net'=>$run->total_net] as $lbl=>$val)
        <div class="card" style="padding:16px;text-align:center;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">{{ $lbl }}</div>
            <div style="font-size:18px;font-weight:700;color:{{ $lbl==='Total Net'?'var(--success)':'var(--navy)' }};">K{{ number_format($val,2) }}</div>
        </div>
        @endforeach
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Employee Payslips</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:rgba(16,7,54,0.04);">
                        @foreach(['Employee','Gross Pay','NAPSA (5%)','NHIMA (1%)','PAYE','Deductions Total','Net Pay'] as $h)
                        <th style="padding:10px 16px;text-align:{{ $loop->index>0?'right':'left' }};font-size:11px;color:var(--text-muted);font-weight:600;white-space:nowrap;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($slips as $slip)
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:10px 16px;">
                            <a href="{{ route('hr.employees.show', $slip->employee) }}" style="font-weight:600;color:var(--navy);text-decoration:none;">{{ $slip->employee->first_name }} {{ $slip->employee->last_name }}</a>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $slip->employee->employee_number }} · {{ $slip->employee->job_title }}</div>
                        </td>
                        <td style="padding:10px 16px;text-align:right;">K{{ number_format($slip->gross_pay,2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">K{{ number_format($slip->napsa_employee,2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">K{{ number_format($slip->nhima_employee,2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">K{{ number_format($slip->paye_tax,2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--error);">K{{ number_format($slip->napsa_employee+$slip->nhima_employee+$slip->paye_tax+$slip->other_deductions,2) }}</td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;color:var(--success);">K{{ number_format($slip->net_pay,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:rgba(16,7,54,0.06);font-weight:700;">
                        <td style="padding:12px 16px;color:var(--navy);">TOTALS</td>
                        <td style="padding:12px 16px;text-align:right;">K{{ number_format($run->total_gross,2) }}</td>
                        <td style="padding:12px 16px;text-align:right;color:var(--error);">K{{ number_format($run->total_napsa,2) }}</td>
                        <td style="padding:12px 16px;text-align:right;color:var(--error);">K{{ number_format($run->total_nhima,2) }}</td>
                        <td style="padding:12px 16px;text-align:right;color:var(--error);">K{{ number_format($run->total_paye,2) }}</td>
                        <td style="padding:12px 16px;text-align:right;color:var(--error);">K{{ number_format($run->total_gross-$run->total_net,2) }}</td>
                        <td style="padding:12px 16px;text-align:right;color:var(--success);">K{{ number_format($run->total_net,2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
