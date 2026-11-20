@extends('layouts.app')
@section('title', 'New Production Order')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Module 02</div><h1 class="page-title">🏭 New Production Order</h1></div>
        <a href="{{ route('production.index') }}" class="btn-outline">← All Orders</a>
    </div>
</div>
<div class="page-body" style="max-width:900px;">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('production.store') }}">
        @csrf
        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Order Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Client *</label>
                    <select name="client_id" class="form-input" required>
                        <option value="">Select client…</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}{{ $c->company ? ' ('.$c->company.')' : '' }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Linked Invoice</label>
                    <select name="invoice_id" class="form-input">
                        <option value="">None</option>
                        @foreach($invoices as $inv)<option value="{{ $inv->id }}" {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>{{ $inv->invoice_number }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Assign To</label>
                    <select name="assigned_to" class="form-input">
                        <option value="">Unassigned</option>
                        @foreach($users as $u)<option value="{{ $u->id }}" {{ old('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Priority *</label>
                    <select name="priority" class="form-input" required>
                        @foreach(['low'=>'Low','normal'=>'Normal','high'=>'High','urgent'=>'Urgent'] as $value=>$label)
                        <option value="{{ $value }}" {{ old('priority','normal') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Vertical *</label>
                    <select name="vertical" class="form-input" required>
                        @foreach(['bags'=>'Bags','furniture'=>'Furniture','interiors'=>'Interiors'] as $value=>$label)
                        <option value="{{ $value }}" {{ old('vertical','bags') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Product Type *</label><input type="text" name="product_type" class="form-input" value="{{ old('product_type') }}" required placeholder="e.g. Leather backpack, 3-seater sofa"></div>
                <div class="form-group"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-input" value="{{ old('quantity', 1) }}" min="1" required></div>
                <div class="form-group"><label class="form-label">Quoted Price (ZMW)</label><input type="number" name="quoted_price" class="form-input" value="{{ old('quoted_price') }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-input" value="{{ old('start_date', date('Y-m-d')) }}"></div>
                <div class="form-group"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-input" value="{{ old('due_date') }}"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Brief</label><textarea name="brief" class="form-input" rows="3" placeholder="Customer requirements and scope of work…">{{ old('brief') }}</textarea></div>
            </div>
        </div>

        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Specifications & Materials</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Measurements</label><textarea name="measurements" class="form-input" rows="3" placeholder="Width: 90cm&#10;Height: 45cm">{{ old('measurements') }}</textarea></div>
                <div class="form-group"><label class="form-label">Colour Specs</label><textarea name="colour_specs" class="form-input" rows="3" placeholder="Frame: Charcoal&#10;Upholstery: Ochre">{{ old('colour_specs') }}</textarea></div>
                <div class="form-group"><label class="form-label">Inventory Material</label>
                    <select name="material_item_id" class="form-input">
                        <option value="">None</option>
                        @foreach($materials as $m)<option value="{{ $m->id }}" {{ old('material_item_id') == $m->id ? 'selected' : '' }}>{{ $m->name }} ({{ $m->sku }}) — {{ $m->quantity }} {{ $m->unit }} available</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Material Quantity</label><input type="number" name="material_quantity" class="form-input" value="{{ old('material_quantity', 0) }}" min="0" step="0.01"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Internal Notes</label><textarea name="internal_notes" class="form-input" rows="2">{{ old('internal_notes') }}</textarea></div>
            </div>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Create Order</button>
            <a href="{{ route('production.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
