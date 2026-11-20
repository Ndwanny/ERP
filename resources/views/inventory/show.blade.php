@extends('layouts.app')
@section('title', $item->name)
@section('content')
@php
$badge = ['in_stock'=>'badge-success','low_stock'=>'badge-gold','out_of_stock'=>'badge-error'][$item->status] ?? 'badge-muted';
@endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">Module 03</div>
            <h1 class="page-title">🧵 {{ $item->name }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_',' ',$item->status)) }}</span>
                <span class="badge badge-muted">{{ $item->sku }}</span>
                <span style="font-size:13px;color:var(--text-muted);">{{ $item->category?->name }}</span>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('inventory.index') }}" class="btn-outline">← All Items</a>
            <a href="{{ route('inventory.usage-report') }}" class="btn-outline">📊 Usage Report</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
        <div>
            <div class="card" style="padding:20px;margin-bottom:16px;text-align:center;">
                <div style="font-size:42px;font-weight:700;color:var(--navy);">{{ $item->quantity }}</div>
                <div style="font-size:13px;color:var(--text-muted);">{{ $item->unit }} in stock</div>
                <div style="font-size:11px;color:var(--text-light);margin-top:6px;">Reorder at {{ $item->min_quantity }} {{ $item->unit }}</div>
            </div>

            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Details</div>
                @php $rows = [
                    'SKU' => $item->sku,
                    'Category' => $item->category?->name ?? '—',
                    'Unit' => $item->unit,
                    'Unit Cost' => $item->unit_cost ? 'ZMW '.number_format($item->unit_cost,2) : '—',
                    'Stock Value' => 'ZMW '.number_format($item->quantity * $item->unit_cost, 2),
                    'Supplier' => $item->supplier ?: '—',
                    'Location' => $item->location ?: '—',
                ]; @endphp
                @foreach($rows as $label => $value)
                <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-light);font-size:12px;">
                    <span style="color:var(--text-muted);width:80px;flex-shrink:0;">{{ $label }}</span><span>{{ $value }}</span>
                </div>
                @endforeach
                @if($item->description)<p style="font-size:12px;color:var(--text-muted);margin-top:12px;">{{ $item->description }}</p>@endif
            </div>

            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Restock</div>
                <form method="POST" action="{{ route('inventory.restock', $item) }}">
                    @csrf
                    <div class="form-group"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-input" min="0.01" step="0.01" required></div>
                    <div class="form-group"><label class="form-label">Reference</label><input type="text" name="reference" class="form-input" placeholder="PO number / supplier ref"></div>
                    <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="2"></textarea></div>
                    <button type="submit" class="btn-gold" style="width:100%;font-size:12px;">+ Restock</button>
                </form>
            </div>

            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Deduct Stock</div>
                <form method="POST" action="{{ route('inventory.deduct', $item) }}">
                    @csrf
                    <div class="form-group"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-input" min="0.01" max="{{ $item->quantity }}" step="0.01" required></div>
                    <div class="form-group"><label class="form-label">Reference</label><input type="text" name="reference" class="form-input" placeholder="Order ID / job card"></div>
                    <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="2"></textarea></div>
                    <button type="submit" class="btn-navy" style="width:100%;font-size:12px;">− Deduct</button>
                </form>
            </div>

            <div class="card" style="padding:20px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Danger Zone</div>
                <form method="POST" action="{{ route('inventory.destroy', $item) }}" onsubmit="return confirm('Delete this inventory item?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline" style="width:100%;font-size:12px;color:var(--error);">Delete Item</button>
                </form>
            </div>
        </div>

        <div>
            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Edit Item</div>
                <form method="POST" action="{{ route('inventory.update', $item) }}">
                    @csrf @method('PUT')
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="form-group"><label class="form-label">Category *</label>
                            <select name="category_id" class="form-input" required>
                                @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">SKU *</label><input type="text" name="sku" class="form-input" value="{{ old('sku', $item->sku) }}" required></div>
                        <div class="form-group" style="grid-column:span 2;"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" value="{{ old('name', $item->name) }}" required></div>
                        <div class="form-group"><label class="form-label">Unit *</label><input type="text" name="unit" class="form-input" value="{{ old('unit', $item->unit) }}" required></div>
                        <div class="form-group"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-input" value="{{ old('quantity', $item->quantity) }}" min="0" step="0.01"></div>
                        <div class="form-group"><label class="form-label">Min Quantity</label><input type="number" name="min_quantity" class="form-input" value="{{ old('min_quantity', $item->min_quantity) }}" min="0" step="0.01"></div>
                        <div class="form-group"><label class="form-label">Unit Cost</label><input type="number" name="unit_cost" class="form-input" value="{{ old('unit_cost', $item->unit_cost) }}" min="0" step="0.01"></div>
                        <div class="form-group"><label class="form-label">Supplier</label><input type="text" name="supplier" class="form-input" value="{{ old('supplier', $item->supplier) }}"></div>
                        <div class="form-group"><label class="form-label">Location</label><input type="text" name="location" class="form-input" value="{{ old('location', $item->location) }}"></div>
                        <div class="form-group" style="grid-column:span 2;"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="2">{{ old('description', $item->description) }}</textarea></div>
                        <div class="form-group" style="grid-column:span 2;"><label class="form-label">Reference (optional)</label><input type="text" name="reference" class="form-input" placeholder="Adjustment reference"></div>
                    </div>
                    <button type="submit" class="btn-gold" style="margin-top:6px;font-size:12px;">✓ Save Changes</button>
                </form>
            </div>

            <div class="card">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:600;color:var(--navy);">Stock Transactions ({{ $item->transactions->count() }})</div>
                @forelse($item->transactions as $transaction)
                    @php
                    $typeBadge = ['restock'=>'badge-success','deduction'=>'badge-gold','adjustment'=>'badge-navy','damage'=>'badge-error','return'=>'badge-muted'][$transaction->type] ?? 'badge-muted';
                    @endphp
                    <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                        <div>
                            <div style="color:var(--navy);font-weight:600;"><span class="badge {{ $typeBadge }}">{{ ucfirst($transaction->type) }}</span> {{ $transaction->quantity }} {{ $item->unit }}</div>
                            <div style="color:var(--text-muted);margin-top:4px;">{{ $transaction->quantity_before }} → {{ $transaction->quantity_after }} · {{ $transaction->user?->name ?? 'System' }}@if($transaction->reference) · {{ $transaction->reference }}@endif</div>
                            @if($transaction->notes)<div style="color:var(--text-light);margin-top:2px;">{{ $transaction->notes }}</div>@endif
                        </div>
                        <div style="color:var(--text-muted);font-size:11px;white-space:nowrap;">{{ $transaction->created_at?->format('d M Y H:i') }}</div>
                    </div>
                @empty
                    <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">No transactions yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
