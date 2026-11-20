@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Ecommerce</div><h1 class="page-title">🛍️ Products</h1></div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('ecommerce.categories') }}" class="btn-outline">Categories</a>
            <a href="{{ route('ecommerce.products.create') }}" class="btn-gold">+ Add Product</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card" style="padding:14px 20px;margin-bottom:16px;">
        <form method="GET" action="{{ route('ecommerce.products') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
            <div class="form-group" style="margin:0;min-width:220px;"><label class="form-label">Filter by Category</label>
                <select name="category_id" class="form-input" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <button type="submit" class="btn-outline">Apply</button>
        </form>
    </div>
    <div class="card">
        <table>
            <thead><tr><th>Product</th><th>Category</th><th>Vertical</th><th style="text-align:right;">Price</th><th style="text-align:right;">Stock</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($products as $product)
            <tr>
                <td>
                    <div style="font-weight:600;color:var(--navy);">{{ $product->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $product->sku }}</div>
                </td>
                <td>{{ $product->category?->name ?? '—' }}</td>
                <td>{{ ucfirst($product->vertical) }}</td>
                <td style="text-align:right;">ZMW {{ number_format($product->price,2) }}</td>
                <td style="text-align:right;">{{ $product->stock_quantity }}</td>
                <td>
                    <span class="badge {{ $product->is_published?'badge-success':'badge-muted' }}">{{ $product->is_published?'Published':'Draft' }}</span>
                    @if($product->featured)<span class="badge badge-gold">Featured</span>@endif
                </td>
                <td style="white-space:nowrap;text-align:right;">
                    <a href="{{ route('ecommerce.products.edit', $product) }}" class="btn-outline" style="font-size:11px;padding:5px 10px;">Edit</a>
                    <form method="POST" action="{{ route('ecommerce.products.destroy', $product) }}" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-outline" style="font-size:11px;padding:5px 10px;color:var(--error);border-color:var(--error);">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No products yet</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($products->hasPages())<div style="padding:16px 20px;">{{ $products->links() }}</div>@endif
    </div>
</div>
@endsection
