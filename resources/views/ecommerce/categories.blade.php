@extends('layouts.app')
@section('title', 'Categories')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Ecommerce</div><h1 class="page-title">🗂 Categories</h1></div>
        <a href="{{ route('ecommerce.products') }}" class="btn-outline">← Products</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Add Category</div>
        <form method="POST" action="{{ route('ecommerce.categories.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. Tote Bags"></div>
            <div class="form-group"><label class="form-label">Vertical *</label>
                <select name="vertical" class="form-input" required>@foreach(['bags'=>'Bags','furniture'=>'Furniture','accessories'=>'Accessories'] as $v=>$l)<option value="{{ $v }}" {{ old('vertical')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
            </div>
            <button type="submit" class="btn-gold" style="width:100%;">+ Add Category</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Product Categories</div>
        </div>
        @forelse($categories as $cat)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
            <div>
                <div style="font-weight:600;color:var(--navy);">{{ $cat->name }}</div>
                @if($cat->vertical)<div style="font-size:11px;color:var(--text-muted);">{{ ucfirst($cat->vertical) }}</div>@endif
            </div>
            <span class="badge badge-muted">{{ $cat->products_count }} products</span>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No categories yet</div>
        @endforelse
    </div>
</div>
@endsection
