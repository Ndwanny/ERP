@extends('layouts.app')
@section('title', 'Edit ' . $invoice->invoice_number)
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Smart Invoicing</div><h1 class="page-title">✏️ Edit {{ $invoice->invoice_number }}</h1></div>
        <a href="{{ route('invoicing.show', $invoice) }}" class="btn-outline">← Back to Invoice</a>
    </div>
</div>
<div class="page-body" style="max-width:1000px;">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:20px;">{{ $errors->first() }}</div>@endif
    @include('invoicing._form', ['invoice' => $invoice, 'clients' => $clients, 'type' => $type])
</div>
@endsection
