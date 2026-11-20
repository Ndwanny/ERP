@extends('layouts.app')
@section('title', 'Shop Orders')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Ecommerce</div><h1 class="page-title">📦 Shop Orders</h1></div>
        <a href="{{ route('ecommerce.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @php $statusColors=['pending'=>'badge-gold','confirmed'=>'badge-navy','in_production'=>'badge-navy','ready'=>'badge-gold','shipped'=>'badge-navy','delivered'=>'badge-success','cancelled'=>'badge-error','refunded'=>'badge-muted']; @endphp
    <div class="card">
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th>Channel</th><th style="text-align:right;">Total</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($orders as $order)
            <tr>
                <td><a href="{{ route('ecommerce.orders.show', $order) }}" style="color:var(--navy);font-weight:600;text-decoration:none;">{{ $order->order_number }}</a></td>
                <td>{{ $order->customer?->name ?? 'Guest' }}</td>
                <td><span class="badge {{ $statusColors[$order->status]??'badge-muted' }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></td>
                <td><span class="badge badge-muted">{{ ucfirst($order->payment_status) }}</span></td>
                <td>{{ ucfirst(str_replace('_',' ',$order->channel)) }}</td>
                <td style="text-align:right;font-weight:600;">ZMW {{ number_format($order->total,2) }}</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $order->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No shop orders yet</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())<div style="padding:16px 20px;">{{ $orders->links() }}</div>@endif
    </div>
</div>
@endsection
