<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #100736; background: #fff; padding: 30px; }
.header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #F9B040; }
.brand { font-size: 18px; font-weight: bold; color: #100736; }
.brand-sub { font-size: 10px; color: #9896a8; margin-top: 2px; }
.doc-type { font-size: 24px; font-weight: bold; color: #100736; text-align: right; }
.doc-number { font-size: 13px; color: #6b6882; margin-top: 4px; text-align: right; }
.parties { display: flex; justify-content: space-between; margin-bottom: 24px; }
.bill-to-label { font-size: 9px; font-weight: bold; letter-spacing: 0.15em; text-transform: uppercase; color: #9896a8; margin-bottom: 4px; }
.bill-to-name { font-size: 15px; font-weight: bold; color: #100736; }
.bill-to-sub { font-size: 11px; color: #6b6882; margin-top: 2px; }
.meta { text-align: right; font-size: 11px; color: #6b6882; }
.meta strong { color: #100736; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
thead { background: #100736; }
th { font-size: 9px; font-weight: bold; letter-spacing: 0.12em; text-transform: uppercase; color: #F9B040; padding: 8px 10px; text-align: left; }
td { padding: 9px 10px; font-size: 11px; border-bottom: 1px solid #f0eef8; }
.totals { margin-left: auto; width: 260px; }
.totals-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 11px; color: #6b6882; border-bottom: 1px solid #f0eef8; }
.totals-final { display: flex; justify-content: space-between; padding: 10px 0 0; font-size: 15px; font-weight: bold; color: #100736; border-top: 2px solid #F9B040; margin-top: 6px; }
.footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #f0eef8; font-size: 10px; color: #9896a8; text-align: center; }
.notes { margin-top: 24px; padding: 12px; background: #F7F5F2; border-radius: 4px; font-size: 11px; color: #6b6882; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="brand">My Perfect Stitch</div>
        <div class="brand-sub">25 Parliament Road, Olympia, Lusaka, Zambia</div>
        <div class="brand-sub">+260 968 531 630 · info@myperfectstitch.co.zm</div>
    </div>
    <div>
        <div class="doc-type">{{ strtoupper($invoice->type) }}</div>
        <div class="doc-number">{{ $invoice->invoice_number }}</div>
    </div>
</div>

<div class="parties">
    <div>
        <div class="bill-to-label">Bill To</div>
        <div class="bill-to-name">{{ $invoice->client->name }}</div>
        @if($invoice->client->company)<div class="bill-to-sub">{{ $invoice->client->company }}</div>@endif
        @if($invoice->client->email)<div class="bill-to-sub">{{ $invoice->client->email }}</div>@endif
        @if($invoice->client->phone)<div class="bill-to-sub">{{ $invoice->client->phone }}</div>@endif
        @if($invoice->client->address)<div class="bill-to-sub">{{ $invoice->client->address }}</div>@endif
    </div>
    <div class="meta">
        <div><strong>Issue Date:</strong> {{ $invoice->issue_date->format('d M Y') }}</div>
        @if($invoice->due_date)<div style="margin-top:4px;"><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</div>@endif
        <div style="margin-top:4px;"><strong>Currency:</strong> {{ $invoice->currency }}</div>
        <div style="margin-top:4px;"><strong>Status:</strong> {{ ucfirst($invoice->status) }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:45%">Description</th>
            <th style="width:10%;text-align:center">Qty</th>
            <th style="width:10%">Unit</th>
            <th style="width:15%;text-align:right">Unit Price</th>
            <th style="width:20%;text-align:right">Total (ZMW)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items->sortBy('sort_order') as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td style="text-align:center">{{ $item->quantity }}</td>
            <td>{{ $item->unit }}</td>
            <td style="text-align:right">{{ number_format($item->unit_price,2) }}</td>
            <td style="text-align:right;font-weight:bold">{{ number_format($item->total,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <div class="totals-row"><span>Subtotal</span><span>ZMW {{ number_format($invoice->subtotal,2) }}</span></div>
    @if($invoice->discount_amount > 0)
    <div class="totals-row"><span>Discount ({{ $invoice->discount_percent }}%)</span><span>−ZMW {{ number_format($invoice->discount_amount,2) }}</span></div>
    @endif
    <div class="totals-row"><span>VAT ({{ $invoice->tax_percent }}%)</span><span>ZMW {{ number_format($invoice->tax_amount,2) }}</span></div>
    <div class="totals-final"><span>Total</span><span>ZMW {{ number_format($invoice->total,2) }}</span></div>
    @if($invoice->amount_paid > 0)
    <div class="totals-row" style="margin-top:8px;"><span>Amount Paid</span><span>ZMW {{ number_format($invoice->amount_paid,2) }}</span></div>
    <div class="totals-row" style="font-weight:bold;color:#100736;"><span>Balance Due</span><span>ZMW {{ number_format($invoice->balance_due,2) }}</span></div>
    @endif
</div>

@if($invoice->notes || $invoice->terms)
<div class="notes">
    @if($invoice->notes)<div><strong>Notes:</strong> {{ $invoice->notes }}</div>@endif
    @if($invoice->terms)<div style="margin-top:6px;"><strong>Terms:</strong> {{ $invoice->terms }}</div>@endif
</div>
@endif

<div class="footer">
    My Perfect Stitch · Bags, Furniture & Interiors · Lusaka, Zambia<br>
    Thank you for your business!
</div>

</body>
</html>
