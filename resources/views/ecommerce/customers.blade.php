@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Ecommerce</div><h1 class="page-title">👥 Customers</h1></div>
        <a href="{{ route('ecommerce.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Retail Customers ({{ $customers->total() }})</div>
        </div>
        @forelse($customers as $cust)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div>
                <div style="font-weight:600;color:var(--navy);">{{ $cust->name }}</div>
                <div style="font-size:12px;color:var(--text-muted);">
                    @if($cust->email){{ $cust->email }} @endif
                    @if($cust->phone)· {{ $cust->phone }}@endif
                </div>
            </div>
            <div style="text-align:right;">
                <span class="badge badge-muted">{{ $cust->orders_count }} orders</span>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Spent: ZMW {{ number_format($cust->total_spent,2) }}</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Since {{ $cust->created_at->format('d M Y') }}</div>
            </div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No retail customers yet</div>
        @endforelse
        @if($customers->hasPages())<div style="padding:16px 20px;">{{ $customers->links() }}</div>@endif
    </div>
</div>
@endsection
