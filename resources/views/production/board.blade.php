@extends('layouts.app')
@section('title', 'Production Board')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 02</div><h1 class="page-title">🏭 Production Board</h1></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('production.index') }}" class="btn-outline">📋 All Orders</a>
            <a href="{{ route('production.calendar') }}" class="btn-outline">📅 Calendar</a>
            <a href="{{ route('production.create') }}" class="btn-gold">+ New Order</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    <div style="display:grid;grid-template-columns:repeat(4,minmax(240px,1fr));gap:14px;overflow-x:auto;align-items:start;">
        @foreach($stages as $stage)
        @php
        $columnOrders = $orders->where('stage', $stage);
        $columnBadge = ['brief'=>'badge-muted','design'=>'badge-gold','production'=>'badge-navy','delivery'=>'badge-success'][$stage];
        @endphp
        <div class="card" style="padding:0;min-height:200px;">
            <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;font-weight:600;color:var(--navy);text-transform:uppercase;letter-spacing:0.08em;">{{ ['brief'=>'1. Brief','design'=>'2. Design','production'=>'3. Production','delivery'=>'4. Delivery'][$stage] }}</span>
                <span class="badge {{ $columnBadge }}">{{ $columnOrders->count() }}</span>
            </div>
            <div style="padding:12px;">
                @forelse($columnOrders as $order)
                <div style="border:1px solid var(--border-light);border-radius:8px;padding:12px;margin-bottom:12px;background:var(--white);">
                    <a href="{{ route('production.show', $order) }}" style="font-size:12px;font-weight:600;color:var(--gold-dark);text-decoration:none;">{{ $order->order_number }}</a>
                    <div style="font-size:13px;color:var(--navy);margin-top:4px;">{{ $order->product_type }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $order->client?->name }}</div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;margin:8px 0;">
                        <span class="badge {{ $order->priority === 'urgent' ? 'badge-error' : ($order->priority === 'high' ? 'badge-gold' : 'badge-muted') }}">{{ ucfirst($order->priority) }}</span>
                        @if($order->qc_passed === true)<span class="badge badge-success">QC Passed</span>@endif
                        @if($order->qc_passed === false)<span class="badge badge-error">QC Failed</span>@endif
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;">Due {{ optional($order->due_date)->format('d M Y') ?? '—' }} · {{ $order->stage_progress }}%</div>
                    <form method="POST" action="{{ route('production.update-stage', $order) }}">
                        @csrf @method('PUT')
                        <select name="stage" class="form-input" style="font-size:11px;padding:6px 8px;margin-bottom:6px;">
                            @foreach(['brief','design','production','delivery','completed','cancelled'] as $option)
                            <option value="{{ $option }}" {{ $order->stage === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="notes" class="form-input" style="font-size:11px;padding:6px 8px;margin-bottom:6px;" placeholder="Stage notes (optional)">
                        <button type="submit" class="btn-navy" style="width:100%;font-size:11px;padding:6px;">Move Stage</button>
                    </form>
                </div>
                @empty
                <div style="padding:24px 8px;text-align:center;color:var(--text-muted);font-size:12px;">No orders</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
