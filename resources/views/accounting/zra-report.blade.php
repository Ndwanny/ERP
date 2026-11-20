@extends('layouts.app')
@section('title', 'ZRA Report')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Accounting</div><h1 class="page-title">🇿🇲 ZRA VAT Report</h1>
            <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">{{ now()->format('F Y') }}</div>
        </div>
        <a href="{{ route('accounting.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:24px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Total Sales This Month</div>
            <div style="font-size:28px;font-weight:700;color:var(--navy);">ZMW {{ number_format($total_sales,2) }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Inclusive of VAT</div>
        </div>
        <div class="card" style="padding:24px;">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">VAT Collected (16%) This Month</div>
            <div style="font-size:28px;font-weight:700;color:var(--gold);">ZMW {{ number_format($vat_collected,2) }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Payable to ZRA</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">ZRA Submission Checklist</div>
        @foreach(['Monthly VAT Return (Form 12)'=>'Due by 18th of following month','PAYE Return (P5)'=>'Due by 14th of following month','NAPSA Monthly Contributions'=>'Due by 10th of following month','NHIMA Monthly Contributions'=>'Due by last day of month'] as $item=>$due)
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
            <div style="font-weight:500;color:var(--navy);">{{ $item }}</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $due }}</div>
        </div>
        @endforeach
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Monthly VAT Summary</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:rgba(16,7,54,0.04);">
                        <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);font-weight:600;">Month</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">Total Sales</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">VAT Collected (16%)</th>
                        <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);font-weight:600;">Net of VAT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($months as $m)
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:10px 16px;font-weight:600;color:var(--navy);">{{ $m['label'] }}</td>
                        <td style="padding:10px 16px;text-align:right;">ZMW {{ number_format($m['total_sales'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--gold);font-weight:600;">ZMW {{ number_format($m['vat_collected'],2) }}</td>
                        <td style="padding:10px 16px;text-align:right;color:var(--success);">ZMW {{ number_format($m['total_sales']-$m['vat_collected'],2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
