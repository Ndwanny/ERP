@extends('layouts.app')
@section('title', 'Order & Production')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 02</div><h1 class="page-title">🏭 Order & Production</h1></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('production.board') }}" class="btn-outline">📋 Board</a>
            <a href="{{ route('production.calendar') }}" class="btn-outline">📅 Calendar</a>
            <a href="{{ route('production.create') }}" class="btn-gold">+ New Order</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Total Orders</div><div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $stats['total'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Active</div><div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $stats['active'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">In Production</div><div style="font-size:24px;font-weight:700;color:var(--gold-dark);">{{ $stats['production'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Completed</div><div style="font-size:24px;font-weight:700;color:var(--success);">{{ $stats['completed'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Urgent</div><div style="font-size:24px;font-weight:700;color:var(--error);">{{ $stats['urgent'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Overdue</div><div style="font-size:24px;font-weight:700;color:var(--error);">{{ $stats['overdue'] }}</div></div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif

    <div class="card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="GET" action="{{ route('production.index') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
            <div class="form-group" style="margin:0;min-width:200px;"><label class="form-label">Stage</label>
                <select name="stage" class="form-input">
                    <option value="">All stages</option>
                    @foreach(['brief','design','production','delivery','completed','cancelled'] as $stage)
                    <option value="{{ $stage }}" {{ request('stage') === $stage ? 'selected' : '' }}>{{ ucfirst($stage) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-navy">Filter</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Order #</th><th>Client</th><th>Product</th><th>Stage</th><th>Progress</th><th>Priority</th><th>Assigned</th><th>Due</th></tr></thead>
            <tbody>
            @forelse($orders as $order)
                @php
                $badge = ['brief'=>'badge-muted','design'=>'badge-gold','production'=>'badge-navy','delivery'=>'badge-gold','completed'=>'badge-success','cancelled'=>'badge-error'][$order->stage] ?? 'badge-muted';
                $priorityBadge = ['low'=>'badge-muted','normal'=>'badge-muted','high'=>'badge-gold','urgent'=>'badge-error'][$order->priority] ?? 'badge-muted';
                @endphp
                <tr>
                    <td><a href="{{ route('production.show', $order) }}" style="color:#C9A84C;">{{ $order->order_number }}</a></td>
                    <td>{{ $order->client?->name }}</td>
                    <td>{{ $order->product_type }} <span style="color:var(--text-light);font-size:11px;">×{{ $order->quantity }}</span></td>
                    <td><span class="badge {{ $badge }}">{{ ucfirst($order->stage) }}</span></td>
                    <td style="font-size:11px;color:var(--text-muted);">{{ $order->stage_progress }}%</td>
                    <td><span class="badge {{ $priorityBadge }}">{{ ucfirst($order->priority) }}</span></td>
                    <td>{{ $order->assignedTo?->name ?? '—' }}</td>
                    <td>{{ $order->due_date?->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">No orders yet</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())<div style="padding:16px 20px;">{{ $orders->links() }}</div>@endif
    </div>
</div>
@endsection
