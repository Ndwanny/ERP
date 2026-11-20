@extends('layouts.app')
@section('title', 'Inventory Usage Report')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 03</div><h1 class="page-title">📊 Inventory Usage Report</h1></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('inventory.low-stock') }}" class="btn-outline">⚠️ Low Stock</a>
            <a href="{{ route('inventory.index') }}" class="btn-outline">← All Items</a>
        </div>
    </div>
</div>
<div class="page-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;">
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Transactions</div><div style="font-size:24px;font-weight:700;color:var(--navy);">{{ $totals['transactions'] }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Restocked</div><div style="font-size:24px;font-weight:700;color:var(--success);">{{ number_format($totals['restocked'], 2) }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Deducted</div><div style="font-size:24px;font-weight:700;color:var(--gold-dark);">{{ number_format($totals['deducted'], 2) }}</div></div>
        <div class="card" style="padding:16px;text-align:center;"><div style="font-size:11px;color:var(--text-muted);">Damaged</div><div style="font-size:24px;font-weight:700;color:var(--error);">{{ number_format($totals['damaged'], 2) }}</div></div>
    </div>

    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif

    <div class="card" style="padding:16px 20px;margin-bottom:16px;">
        <form method="GET" action="{{ route('inventory.usage-report') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div class="form-group" style="margin:0;min-width:180px;"><label class="form-label">Item</label>
                <select name="item_id" class="form-input">
                    <option value="">All items</option>
                    @foreach($items as $item)<option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} ({{ $item->sku }})</option>@endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:140px;"><label class="form-label">Type</label>
                <select name="type" class="form-input">
                    <option value="">All types</option>
                    @foreach(['restock','deduction','adjustment','damage','return'] as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">From</label><input type="date" name="from" class="form-input" value="{{ request('from') }}"></div>
            <div class="form-group" style="margin:0;"><label class="form-label">To</label><input type="date" name="to" class="form-input" value="{{ request('to') }}"></div>
            <button type="submit" class="btn-navy">Generate</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Date</th><th>Item</th><th>Type</th><th>Quantity</th><th>Before → After</th><th>Reference</th><th>Recorded By</th></tr></thead>
            <tbody>
            @forelse($transactions as $transaction)
                @php
                $badge = ['restock'=>'badge-success','deduction'=>'badge-gold','adjustment'=>'badge-navy','damage'=>'badge-error','return'=>'badge-muted'][$transaction->type] ?? 'badge-muted';
                @endphp
                <tr>
                    <td style="font-size:11px;color:var(--text-muted);">{{ $transaction->created_at?->format('d M Y H:i') }}</td>
                    <td><a href="{{ route('inventory.show', $transaction->item_id) }}" style="color:#C9A84C;">{{ $transaction->item?->name ?? '—' }}</a><div style="font-size:11px;color:var(--text-light);">{{ $transaction->item?->sku }}</div></td>
                    <td><span class="badge {{ $badge }}">{{ ucfirst($transaction->type) }}</span></td>
                    <td>{{ $transaction->quantity }} {{ $transaction->item?->unit }}</td>
                    <td style="font-size:11px;color:var(--text-muted);">{{ $transaction->quantity_before }} → {{ $transaction->quantity_after }}</td>
                    <td>{{ $transaction->reference ?: '—' }}</td>
                    <td>{{ $transaction->user?->name ?? 'System' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No transactions match these filters</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($transactions->hasPages())<div style="padding:16px 20px;">{{ $transactions->links() }}</div>@endif
    </div>
</div>
@endsection
