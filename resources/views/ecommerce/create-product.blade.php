@extends('layouts.app')
@section('title', 'Add Product')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Ecommerce</div><h1 class="page-title">🛍️ Add Product</h1></div>
        <a href="{{ route('ecommerce.products') }}" class="btn-outline">← Products</a>
    </div>
</div>
<div class="page-body">
    <form method="POST" action="{{ route('ecommerce.products.store') }}" style="max-width:800px;">
        @csrf
        @if($errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif
        <div class="card" style="padding:24px;margin-bottom:20px;">
            <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Product Details</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="Product name"></div>
                <div class="form-group"><label class="form-label">Category *</label>
                    <select name="category_id" class="form-input" required>
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}{{ $cat->vertical?' ('.ucfirst($cat->vertical).')':'' }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Vertical *</label>
                    <select name="vertical" class="form-input" required>@foreach(['bags'=>'Bags','furniture'=>'Furniture','accessories'=>'Accessories'] as $v=>$l)<option value="{{ $v }}" {{ old('vertical')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">SKU *</label><input type="text" name="sku" class="form-input" value="{{ old('sku') }}" required placeholder="MPS-0001"></div>
                <div class="form-group"><label class="form-label">Stock Quantity *</label><input type="number" name="stock_quantity" class="form-input" value="{{ old('stock_quantity',0) }}" required min="0"></div>
                <div class="form-group"><label class="form-label">Price (ZMW) *</label><input type="number" name="price" class="form-input" value="{{ old('price') }}" required min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Compare-at Price (ZMW)</label><input type="number" name="compare_price" class="form-input" value="{{ old('compare_price') }}" min="0" step="0.01"></div>
                <div class="form-group" style="grid-column:span 2;"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="3" placeholder="Product description…">{{ old('description') }}</textarea></div>
            </div>
            <div style="display:flex;gap:24px;margin-top:8px;flex-wrap:wrap;">
                <label style="font-size:13px;display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_published" value="1" {{ old('is_published')?'checked':'' }}> Published</label>
                <label style="font-size:13px;display:flex;align-items:center;gap:8px;"><input type="checkbox" name="featured" value="1" {{ old('featured')?'checked':'' }}> Featured</label>
                <label style="font-size:13px;display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_custom_order" value="1" {{ old('is_custom_order')?'checked':'' }}> Custom Order</label>
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn-gold">✓ Create Product</button>
            <a href="{{ route('ecommerce.products') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
