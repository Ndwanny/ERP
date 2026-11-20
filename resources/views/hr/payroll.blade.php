@extends('layouts.app')
@section('title', 'Payroll')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">HR & Payroll</div><h1 class="page-title">💵 Payroll Processing</h1></div>
        <a href="{{ route('hr.employees') }}" class="btn-outline">← Employees</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.08em;">Run Payroll</div>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">Calculates PAYE, NAPSA (5%) and NHIMA (1%) for all active monthly employees.</p>
        <form method="POST" action="{{ route('hr.payroll.run') }}">
            @csrf
            <div class="form-group"><label class="form-label">Payroll Period *</label><input type="month" name="period" class="form-input" value="{{ date('Y-m') }}" required></div>
            <div class="form-group"><label class="form-label">Pay Date *</label><input type="date" name="pay_date" class="form-input" value="{{ date('Y-m-d') }}" required></div>
            <button type="submit" class="btn-gold" style="width:100%;" onclick="return confirm('Run payroll for this period? This cannot be reversed.')">▶ Run Payroll</button>
        </form>

        <div style="margin-top:20px;padding:16px;background:rgba(16,7,54,0.04);border-radius:8px;">
            <div style="font-size:11px;font-weight:600;color:var(--navy);margin-bottom:8px;">ZRA PAYE Bands (2024)</div>
            @foreach(['≤ K4,800/mo'=>'0%','K4,801–K5,800/mo'=>'25%','K5,801–K9,200/mo'=>'30%','> K9,200/mo'=>'37%'] as $band=>$rate)
            <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);padding:3px 0;">
                <span>{{ $band }}</span><strong>{{ $rate }}</strong>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Payroll Runs</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $runs->total() }} runs</div>
        </div>
        @php $runColors=['draft'=>'badge-muted','approved'=>'badge-gold','processed'=>'badge-success']; @endphp
        @forelse($runs as $run)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <span style="font-weight:600;font-size:14px;color:var(--navy);">{{ $run->period }}</span>
                    <span class="badge {{ $runColors[$run->status]??'badge-muted' }}" style="font-size:10px;margin-left:8px;">{{ ucfirst($run->status) }}</span>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Pay date: {{ \Carbon\Carbon::parse($run->pay_date)->format('d M Y') }}</div>
                </div>
                <div style="text-align:right;">
                    <a href="{{ route('hr.payroll.slips', $run->period) }}" class="btn-outline" style="font-size:11px;padding:6px 12px;">View Payslips</a>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border-light);">
                @foreach(['Gross'=>$run->total_gross,'PAYE'=>$run->total_paye,'NAPSA'=>$run->total_napsa,'NHIMA'=>$run->total_nhima,'Net Pay'=>$run->total_net] as $lbl=>$val)
                <div style="text-align:center;padding:8px;background:rgba(16,7,54,0.03);border-radius:6px;">
                    <div style="font-size:10px;color:var(--text-muted);margin-bottom:2px;">{{ $lbl }}</div>
                    <div style="font-size:12px;font-weight:600;color:{{ $lbl==='Net Pay'?'var(--success)':'var(--navy)' }};">K{{ number_format($val,0) }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No payroll runs yet. Use the form to run the first payroll.</div>
        @endforelse
        @if($runs->hasPages())
        <div style="padding:16px 20px;">{{ $runs->links() }}</div>
        @endif
    </div>
</div>
@endsection
