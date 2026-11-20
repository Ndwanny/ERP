@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Accounting</div><h1 class="page-title">📉 Expenses</h1></div>
        <a href="{{ route('accounting.index') }}" class="btn-outline">← Dashboard</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Record Expense</div>
        <form method="POST" action="{{ route('accounting.expenses.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-input" value="{{ old('title') }}" required placeholder="e.g. Fabric purchase"></div>
            <div class="form-group"><label class="form-label">Amount (ZMW) *</label><input type="number" name="amount" class="form-input" value="{{ old('amount') }}" min="0.01" step="0.01" required></div>
            <div class="form-group"><label class="form-label">Date *</label><input type="date" name="expense_date" class="form-input" value="{{ old('expense_date', date('Y-m-d')) }}" required></div>
            <div class="form-group"><label class="form-label">Category *</label>
                <select name="category" class="form-input" required>
                    @foreach($categories as $c)<option value="{{ $c }}" {{ old('category')==$c?'selected':'' }}>{{ ucfirst($c) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Payment Method *</label>
                <select name="payment_method" class="form-input" required>
                    @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','mobile_money'=>'Mobile Money','card'=>'Card'] as $v=>$l)<option value="{{ $v }}" {{ old('payment_method')==$v?'selected':'' }}>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-input"><option value="">No supplier</option>@foreach($suppliers as $s)<option value="{{ $s->id }}" {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}{{ $s->company?' ('.$s->company.')':'' }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Vertical</label>
                <select name="vertical" class="form-input"><option value="">All</option>@foreach(['bags','furniture','interiors','admin','shared'] as $v)<option value="{{ $v }}" {{ old('vertical')==$v?'selected':'' }}>{{ ucfirst($v) }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="2">{{ old('description') }}</textarea></div>
            <button type="submit" class="btn-gold" style="width:100%;">✓ Record Expense</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Expense Records</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $expenses->total() }} records</div>
        </div>
        @forelse($expenses as $exp)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);display:grid;grid-template-columns:2fr 1fr 1fr 1fr;align-items:center;gap:8px;font-size:13px;">
            <div>
                <div style="font-weight:600;color:var(--navy);">{{ $exp->title }}</div>
                <div style="font-size:11px;color:var(--text-muted);">
                    {{ ucfirst($exp->category) }} · {{ $exp->expense_date->format('d M Y') }}
                    @if($exp->supplier) · {{ $exp->supplier->name }}@endif
                </div>
                @if($exp->description)<div style="font-size:11px;color:var(--text-light);">{{ $exp->description }}</div>@endif
            </div>
            <div style="font-size:11px;color:var(--text-muted);">{{ ucwords(str_replace('_',' ',$exp->payment_method)) }}@if($exp->vertical)<br><span class="badge badge-muted" style="font-size:9px;">{{ $exp->vertical }}</span>@endif</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $exp->recordedBy?->name??'—' }}</div>
            <div style="text-align:right;font-weight:700;color:var(--error);">ZMW {{ number_format($exp->amount,2) }}</div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No expenses recorded yet</div>
        @endforelse
        @if($expenses->hasPages())
        <div style="padding:16px 20px;">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>
@endsection
