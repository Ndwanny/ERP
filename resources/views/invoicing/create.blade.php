@extends('layouts.app')
@section('title', $type === 'quote' ? 'New Quote' : 'New Invoice')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Smart Invoicing</div><h1 class="page-title">🧾 {{ $type === 'quote' ? 'New Quote' : 'New Invoice' }}</h1></div>
        <a href="{{ route('invoicing.index') }}" class="btn-outline">← All Invoices</a>
    </div>
</div>
<div class="page-body" style="max-width:1000px;">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif
    @include('invoicing._form', ['invoice' => null, 'clients' => $clients, 'type' => $type])
</div>
@endsection
