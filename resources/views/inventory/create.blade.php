@extends('layouts.app')
@section('title', 'Add Inventory Item')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 03</div><h1 class="page-title">🧵 Add Inventory Item</h1></div>
        <a href="{{ route('inventory.index') }}" class="btn-outline">← All Items</a>
    </div>
</div>
<div class="page-body" style="max-width:800px;">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('inventory.store') }}">
        @csrf
        <div class="card" style="padding:24px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Item Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Category *</label>
                    <select name="category_id" class="form-input" required>
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ ucfirst($cat->vertical) }})</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">SKU *</label><input type="text" name="sku" class="form-input" value="{{ old('sku') }}" required placeholder="e.g. FAB-CANVAS-001"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Item Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. Canvas Fabric 12oz"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="2">{{ old('description') }}</textarea></div>
                <div class="form-group"><label class="form-label">Unit *</label><input type="text" name="unit" class="form-input" value="{{ old('unit', 'pcs') }}" required placeholder="metres, kg, rolls, pcs"></div>
                <div class="form-group"><label class="form-label">Initial Quantity</label><input type="number" name="quantity" class="form-input" value="{{ old('quantity', 0) }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Minimum Quantity (Reorder Point)</label><input type="number" name="min_quantity" class="form-input" value="{{ old('min_quantity', 0) }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Unit Cost (ZMW)</label><input type="number" name="unit_cost" class="form-input" value="{{ old('unit_cost', 0) }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Supplier</label><input type="text" name="supplier" class="form-input" value="{{ old('supplier') }}" placeholder="Supplier name"></div>
                <div class="form-group"><label class="form-label">Storage Location</label><input type="text" name="location" class="form-input" value="{{ old('location') }}" placeholder="e.g. Shelf A3"></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="submit" class="btn-gold">✓ Add Item</button>
                <a href="{{ route('inventory.index') }}" class="btn-outline">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
