@extends('layouts.app')
@section('title', 'Ecommerce')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:rgba(201,168,76,0.5);margin-bottom:6px;">Module 06</div><h1 class="page-title">🛍️ Ecommerce</h1></div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('ecommerce.products') }}" class="btn-outline">Products</a>
            <a href="{{ route('ecommerce.categories') }}" class="btn-outline">Categories</a>
            <a href="{{ route('ecommerce.orders') }}" class="btn-gold">Shop Orders</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
        <a href="{{ route('ecommerce.orders') }}" class="card" style="padding:20px;text-decoration:none;display:block;">
            <div style="font-size:10px;letter-spacing:0.15em;text-transform:uppercase;color:rgba(201,168,76,0.5);">Shop Orders</div>
            <div style="font-size:32px;font-weight:700;color:var(--navy);margin-top:4px;">{{ $total_orders }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $pending_orders }} pending</div>
        </a>
        <a href="{{ route('ecommerce.products') }}" class="card" style="padding:20px;text-decoration:none;display:block;">
            <div style="font-size:10px;letter-spacing:0.15em;text-transform:uppercase;color:rgba(201,168,76,0.5);">Published Products</div>
            <div style="font-size:32px;font-weight:700;color:var(--gold);margin-top:4px;">{{ $total_products }}</div>
        </a>
        <a href="{{ route('ecommerce.customers') }}" class="card" style="padding:20px;text-decoration:none;display:block;">
            <div style="font-size:10px;letter-spacing:0.15em;text-transform:uppercase;color:rgba(201,168,76,0.5);">Customers</div>
            <div style="font-size:32px;font-weight:700;color:var(--navy);margin-top:4px;">{{ $total_customers }}</div>
        </a>
        <a href="{{ route('ecommerce.categories') }}" class="card" style="padding:20px;text-decoration:none;display:block;">
            <div style="font-size:10px;letter-spacing:0.15em;text-transform:uppercase;color:rgba(201,168,76,0.5);">Categories</div>
            <div style="font-size:32px;font-weight:700;color:var(--text-muted);margin-top:4px;">—</div>
        </a>
    </div>
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Recent Orders</div>
            <a href="{{ route('ecommerce.orders') }}" class="btn-outline" style="font-size:11px;padding:6px 12px;">View All</a>
        </div>
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th style="text-align:right;">Total</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($recent_orders as $order)
            <tr>
                <td><a href="{{ route('ecommerce.orders.show', $order) }}" style="color:var(--navy);font-weight:600;text-decoration:none;">{{ $order->order_number }}</a></td>
                <td>{{ $order->customer?->name ?? 'Guest' }}</td>
                <td><span class="badge badge-muted">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></td>
                <td style="text-align:right;">ZMW {{ number_format($order->total,2) }}</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $order->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">No orders yet</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
