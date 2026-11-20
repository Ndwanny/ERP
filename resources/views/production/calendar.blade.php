@extends('layouts.app')
@section('title', 'Production Calendar')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 02</div><h1 class="page-title">📅 Production Calendar</h1></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('production.index') }}" class="btn-outline">📋 All Orders</a>
            <a href="{{ route('production.board') }}" class="btn-outline">📋 Board</a>
            <a href="{{ route('production.create') }}" class="btn-gold">+ New Order</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif

    <div class="card" style="padding:16px 20px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <a href="{{ route('production.calendar', ['month' => $previous]) }}" class="btn-outline" style="font-size:12px;">← Previous</a>
        <div style="font-size:16px;font-weight:600;color:var(--navy);">{{ $start->format('F Y') }}</div>
        <a href="{{ route('production.calendar', ['month' => $next]) }}" class="btn-outline" style="font-size:12px;">Next →</a>
    </div>

    <div class="card" style="overflow-x:auto;">
        <div style="display:grid;grid-template-columns:repeat(7,minmax(130px,1fr));min-width:910px;">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dayName)
            <div style="padding:10px;text-align:center;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;border-bottom:1px solid var(--border);">{{ $dayName }}</div>
            @endforeach
            @foreach($weeks as $week)
                @foreach($week as $day)
                <div style="min-height:110px;padding:8px;border-right:1px solid var(--border-light);border-bottom:1px solid var(--border-light);background:{{ $day['inMonth'] ? 'var(--white)' : 'var(--cream)' }};">
                    <div style="font-size:11px;color:{{ $day['date']->isToday() ? 'var(--gold-dark)' : ($day['inMonth'] ? 'var(--text-muted)' : 'var(--text-light)') }};font-weight:{{ $day['date']->isToday() ? '700' : '400' }};margin-bottom:6px;">{{ $day['date']->format('d M') }}</div>
                    @foreach($day['orders'] as $order)
                    <a href="{{ route('production.show', $order) }}" style="display:block;font-size:10px;padding:4px 5px;margin-bottom:4px;border-radius:4px;background:rgba(249,176,64,0.12);color:var(--navy);text-decoration:none;line-height:1.3;">
                        <strong>{{ $order->order_number }}</strong><br>{{ $order->product_type }}
                    </a>
                    @endforeach
                </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
@endsection
