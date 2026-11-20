@extends('layouts.app')
@section('title', $order->order_number)
@section('content')
@php $statusColors=['pending'=>'badge-gold','confirmed'=>'badge-navy','in_production'=>'badge-navy','ready'=>'badge-gold','shipped'=>'badge-navy','delivered'=>'badge-success','cancelled'=>'badge-error','refunded'=>'badge-muted']; @endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">Ecommerce</div>
            <h1 class="page-title">📦 {{ $order->order_number }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;">
                <span class="badge {{ $statusColors[$order->status]??'badge-muted' }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
            </div>
        </div>
        <a href="{{ route('ecommerce.orders') }}" class="btn-outline">← Orders</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Order Items</div>
            </div>
            @foreach($order->items as $item)
            <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <div>
                    <div style="font-weight:600;color:var(--navy);">{{ $item->product_name ?: ($item->product?->name ?? 'Product') }}</div>
                    <div style="font-size:12px;color:var(--text-muted);">SKU: {{ $item->product?->sku ?? '—' }} · Qty: {{ $item->quantity }} × ZMW {{ number_format($item->unit_price??0,2) }}</div>
                    @if($item->variant_options)
                    <div style="font-size:11px;color:var(--text-light);">
                        @foreach($item->variant_options as $key=>$value)
                            {{ $key }}: {{ $value }}@if(!$loop->last), @endif
                        @endforeach
                    </div>
                    @endif
                </div>
                <div style="font-weight:700;color:var(--navy);">ZMW {{ number_format($item->total ?? (($item->quantity??1)*($item->unit_price??0)),2) }}</div>
            </div>
            @endforeach
            <div style="padding:16px 20px;text-align:right;font-size:13px;">
                <div style="color:var(--text-muted);">Subtotal: ZMW {{ number_format($order->subtotal,2) }}</div>
                @if($order->delivery_fee)<div style="color:var(--text-muted);">Delivery: ZMW {{ number_format($order->delivery_fee,2) }}</div>@endif
                <div style="font-size:16px;font-weight:700;color:var(--navy);margin-top:8px;">Total: ZMW {{ number_format($order->total,2) }}</div>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Order Info</div>
            @foreach(['Customer'=>$order->customer?->name??'Guest','Payment'=>ucwords(str_replace('_',' ',$order->payment_status??'')), 'Method'=>ucwords(str_replace('_',' ',$order->payment_method??'')),'Delivery'=>ucwords(str_replace('_',' ',$order->delivery_method??'')),'Channel'=>ucfirst($order->channel??'—'),'Date'=>$order->created_at->format('d M Y')] as $l=>$v)
            <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-light);font-size:12px;">
                <span style="color:var(--text-muted);width:70px;flex-shrink:0;">{{ $l }}</span>
                <span>{{ $v }}</span>
            </div>
            @endforeach
            @if($order->delivery_address)<div style="margin-top:10px;font-size:12px;color:var(--text-muted);">📍 {{ $order->delivery_address }}</div>@endif
            @if($order->notes)<div style="margin-top:8px;font-size:12px;color:var(--text-muted);">{{ $order->notes }}</div>@endif
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Update Status</div>
            <form method="POST" action="{{ route('ecommerce.orders.update-status', $order) }}">
                @csrf @method('PUT')
                <div class="form-group"><select name="status" class="form-input">@foreach(['pending','confirmed','in_production','ready','shipped','delivered','cancelled','refunded'] as $s)<option value="{{ $s }}" {{ $order->status==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
                <button type="submit" class="btn-gold" style="width:100%;">Update Status</button>
            </form>
        </div>
    </div>
</div>
@endsection
