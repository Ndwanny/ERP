@extends('layouts.app')
@section('title', 'Low Stock Alerts')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 03</div><h1 class="page-title">⚠️ Low Stock Alerts</h1></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('inventory.index') }}" class="btn-outline">← All Items</a>
            <a href="{{ route('inventory.usage-report') }}" class="btn-outline">📊 Usage Report</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(2,minmax(150px,1fr));gap:12px;margin-bottom:20px;max-width:400px;">
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Low Stock</div><div style="font-size:24px;font-weight:700;color:var(--gold-dark);">{{ $stats['low_stock'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Out of Stock</div><div style="font-size:24px;font-weight:700;color:var(--error);">{{ $stats['out_of_stock'] }}</div></div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif

    <div class="card">
        <table>
            <thead><tr><th>SKU</th><th>Item</th><th>Category</th><th>On Hand</th><th>Minimum</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($items as $item)
            <tr>
                <td style="font-size:11px;color:var(--text-light);">{{ $item->sku }}</td>
                <td><a href="{{ route('inventory.show', $item) }}" style="color:#C9A84C;">{{ $item->name }}</a></td>
                <td>{{ $item->category?->name }}</td>
                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                <td>{{ $item->min_quantity }} {{ $item->unit }}</td>
                <td><span class="badge {{ $item->status === 'out_of_stock' ? 'badge-error' : 'badge-gold' }}">{{ ucfirst(str_replace('_',' ',$item->status)) }}</span></td>
                <td><a href="{{ route('inventory.show', $item) }}" style="color:var(--gold-dark);font-size:12px;">Restock →</a></td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No low stock alerts</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($items->hasPages())<div style="padding:16px 20px;">{{ $items->links() }}</div>@endif
    </div>
</div>
@endsection
