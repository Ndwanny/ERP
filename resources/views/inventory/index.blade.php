@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 03</div><h1 class="page-title">🧵 Inventory</h1></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('inventory.usage-report') }}" class="btn-outline">📊 Usage Report</a>
            <a href="{{ route('inventory.low-stock') }}" class="btn-outline">⚠️ Low Stock</a>
            <a href="{{ route('inventory.create') }}" class="btn-gold">+ Add Item</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Total Items</div><div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $stats['total'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">In Stock</div><div style="font-size:24px;font-weight:700;color:var(--success);">{{ $stats['in_stock'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Low Stock</div><div style="font-size:24px;font-weight:700;color:var(--gold-dark);">{{ $stats['low_stock'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Out of Stock</div><div style="font-size:24px;font-weight:700;color:var(--error);">{{ $stats['out_of_stock'] }}</div></div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif

    <div class="card">
        <table>
            <thead><tr><th>SKU</th><th>Item</th><th>Category</th><th>Quantity</th><th>Min</th><th>Unit Cost</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                @php
                $badge = ['in_stock'=>'badge-success','low_stock'=>'badge-gold','out_of_stock'=>'badge-error'][$item->status] ?? 'badge-muted';
                @endphp
                <tr>
                    <td style="color:var(--text-light);font-size:11px;">{{ $item->sku }}</td>
                    <td><a href="{{ route('inventory.show', $item) }}" style="color:#C9A84C;">{{ $item->name }}</a></td>
                    <td>{{ $item->category?->name }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                    <td>{{ $item->min_quantity }}</td>
                    <td>{{ $item->unit_cost ? 'K'.number_format($item->unit_cost, 2) : '—' }}</td>
                    <td><span class="badge {{ $badge }}">{{ ucfirst(str_replace('_',' ',$item->status)) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No inventory items yet</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($items->hasPages())<div style="padding:16px 20px;">{{ $items->links() }}</div>@endif
    </div>
</div>
@endsection
