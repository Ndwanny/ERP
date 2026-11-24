@extends('layouts.app')
@section('title', $invoice ? 'Edit Quote Builder' : 'Advanced Quote Builder')
@push('styles')
@vite(['resources/css/quote-builder.css'])
@endpush
@section('content')
<div class="page-header">
  <div class="page-header-inner">
    <div>
      <div class="page-subtitle">Smart Invoicing / Advanced</div>
      <h1 class="page-title">{{ $invoice ? 'Edit Quote Builder: '.$invoice->invoice_number : 'Advanced Quote Builder' }}</h1>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="{{ route('invoicing.quotes.create') }}" class="btn-outline">Simple Quote</a>
      <a href="{{ route('invoicing.index') }}" class="btn-outline">All Invoices</a>
    </div>
  </div>
</div>
<div class="page-body">
  @if($errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif
  <div class="qb">
    <div class="tabs no-print">
      <div class="tab on" data-pane="p-overview">Price Overview</div>
      <div class="tab" data-pane="p-build">Build Quote</div>
      <div class="tab" data-pane="p-sales">Sales Quote</div>
    </div>

    <div class="pane on" id="p-overview">
      <div class="card">
        <div class="sec">Channel Price Overview (from product catalog)</div>
        <div style="overflow:auto">
          <table>
            <thead><tr><th>Product</th><th class="r">Cost</th><th class="r">MPS Retail</th><th class="r">Partner</th><th class="r">Institutional</th><th class="r">Employee</th><th class="r">Wholesale</th><th class="r">Model List</th><th class="r">Intl USD</th><th>Category</th></tr></thead>
            <tbody id="overviewBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="pane" id="p-build">
      <div class="two">
        <div>
          <div class="card">
            <div class="sec">Add line item</div>
            <div class="field"><label class="lbl">Product</label><select id="product_id"></select></div>
            <div style="display:grid;grid-template-columns:90px 1fr;gap:8px">
              <div class="field"><label class="lbl">Qty</label><input id="quantity" type="number" min="1" value="1"></div>
              <div class="field"><label class="lbl">Channel</label><select id="channel"><option value="retail">MPS Retail</option><option value="partner">Retail Partner</option><option value="institutional">Institutional</option><option value="employee">Employee (20%)</option><option value="wholesale">Wholesale</option></select></div>
            </div>
            <div class="field"><label class="lbl">Branding</label><div class="pills" id="brandPills"><div class="pill on" data-value="none">No branding</div><div class="pill" data-value="screen">Screen</div><div class="pill" data-value="embroid">Embroidery</div><div class="pill" data-value="full">Full picture</div><div class="pill" data-value="leather_patch">Leather patch</div></div></div>
            <div class="field"><label class="lbl">Payment terms</label><div class="pills" id="payPills"><div class="pill on" data-value="0">Upfront</div><div class="pill" data-value="15">15 days</div><div class="pill" data-value="30">30 days</div><div class="pill" data-value="60">60 days</div><div class="pill" data-value="90">90 days</div></div></div>
            <div class="field"><label style="display:flex;gap:8px;align-items:center;font-size:12px"><input type="checkbox" id="rush"> Rush order (+premium)</label></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
              <div class="field"><label class="lbl">Manual discount %</label><input id="manual_discount" type="number" min="0" max="80" step="0.01" value="0"></div>
              <div class="field"><label class="lbl">Strategic discount %</label><input id="strategic_discount" type="number" min="0" max="80" step="0.01" placeholder="0"><div class="pills" id="stratPills" style="margin-top:4px"><div class="pill on" data-value="0">None</div><div class="pill" data-value="5">5%</div><div class="pill" data-value="10">10%</div><div class="pill" data-value="15">15%</div></div></div>
            </div>
            <button type="button" id="addLine" class="btn btn-navy" style="width:100%;margin-top:4px">+ Add to Quote</button>
          </div>

          <div class="card" style="margin-top:14px">
            <div class="sec">Quote line items</div>
            <div class="qi-row" style="border-bottom:1px solid var(--qb-g2);padding-bottom:8px;margin-bottom:4px"><div class="qi-hd">Product</div><div class="qi-hd c">Qty</div><div class="qi-hd r qi-hide">Channel</div><div class="qi-hd r">Unit</div><div class="qi-hd r">Subtotal</div><div></div></div>
            <div id="lineItems"></div>
            <div style="border-top:2px solid var(--qb-g2);margin-top:10px;padding-top:10px;max-width:280px;margin-left:auto">
              <div style="display:flex;justify-content:space-between;padding:3px 0;font-size:12px"><span>Subtotal</span><span id="ltSubtotal">K0</span></div>
              <div style="display:flex;justify-content:space-between;padding:3px 0;font-size:12px"><span>Doc discount</span><span id="ltDisc">-K0</span></div>
              <div style="display:flex;justify-content:space-between;padding:3px 0;font-size:12px"><span>VAT</span><span id="ltVat">K0</span></div>
              <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:15px;font-weight:700;border-top:1px solid var(--qb-g2);margin-top:4px"><span>Total</span><span id="ltTotal">K0</span></div>
            </div>
          </div>
        </div>

        <div>
          <div class="card">
            <div class="sec">Client & Quote details</div>
            <div class="field"><label class="lbl">Client *</label><select id="client_id"><option value="">Select client...</option>@foreach($config['clients'] as $c)<option value="{{ $c->id }}">{{ $c->name }}{{ $c->company ? ' ('.$c->company.')' : '' }}</option>@endforeach</select></div>
            <div class="two"><div class="field"><label class="lbl">Issue date</label><input id="issue_date" type="date"></div><div class="field"><label class="lbl">Valid days</label><input id="valid_days" type="number" min="0" value="30"></div></div>
            <div class="two"><div class="field"><label class="lbl">Due date</label><input id="due_date" type="date"></div><div class="field"><label class="lbl">Currency</label><input id="currency" type="text" value="ZMW"></div></div>
            <div class="two"><div class="field"><label class="lbl">Doc discount %</label><input id="discount_percent" type="number" min="0" max="100" step="0.01" value="0"></div><div class="field"><label class="lbl">VAT %</label><input id="tax_percent" type="number" min="0" max="100" step="0.01" value="16"></div></div>
            <div class="field"><label class="lbl">Vertical</label><select id="vertical"><option value="">—</option><option value="bags">Bags</option><option value="furniture">Furniture</option><option value="interiors">Interiors</option><option value="mixed">Mixed</option></select></div>
            <div class="field"><label class="lbl">Lead time</label><input id="lead_time" type="text" placeholder="e.g. 3–4 weeks from deposit"></div>
            <div class="field"><label class="lbl">Prepared by</label><input id="prepared_by" type="text" value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}"></div>
            <div class="field"><label class="lbl">Notes</label><textarea id="notes" rows="2"></textarea></div>
            <div class="field"><label class="lbl">Terms</label><textarea id="terms" rows="3"></textarea></div>
            <div style="display:flex;gap:8px;margin-top:8px"><button type="button" id="saveQuote" class="btn btn-navy" style="flex:1">Save Quote</button><button type="button" id="genQuote" class="btn btn-teal" style="flex:1">Preview Sales Quote</button></div>
          </div>
          <div class="card" style="margin-top:14px"><div class="sec">Internal margin summary</div><div id="marginSummary"></div></div>
        </div>
      </div>
    </div>

    <div class="pane" id="p-sales">
      <div class="card no-print" style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:10px"><div style="font-size:12px;color:var(--qb-g3)">Client-facing preview. Internal margins/costs are not shown.</div><button type="button" class="btn btn-outline" onclick="window.print()">Print / Save PDF</button></div>
      <div class="sq-wrap" id="salesPane">
        <div class="sq-header">
          <div><div style="font-size:22px;font-weight:800;color:var(--qb-navy)">My Perfect Stitch</div><div style="font-size:12px;color:#666">25 Parliament Road, Olympia, Lusaka, Zambia</div><div style="font-size:12px;color:#666">+260 968 531 630 • info@myperfectstitch.co.zm</div></div>
          <div style="text-align:right"><div style="font-size:24px;font-weight:800;color:var(--qb-navy)">SALES QUOTE</div><div id="sqNum" style="font-size:13px;color:#666">{{ $invoice?->invoice_number ?? 'DRAFT-QUOTE' }}</div><div style="font-size:12px;color:#666">Date: <span id="sqDate"></span></div><div style="font-size:12px;color:#666">Valid until: <span id="sqValid"></span></div></div>
        </div>
        <div style="margin-bottom:14px"><div style="font-size:10px;font-weight:700;color:#888;text-transform:uppercase">Prepared for</div><div id="sqClient"></div></div>
        <table class="sq-tbl" style="width:100%;margin-bottom:14px"><thead><tr><th>Description</th><th class="r">Qty</th><th class="r">Unit Price</th><th class="r">Amount</th></tr></thead><tbody id="sqItems"></tbody></table>
        <div class="totals"><div class="tot-row"><span>Subtotal</span><span id="sqSubtotal">K0</span></div><div class="tot-row"><span>VAT</span><span id="sqVat">K0</span></div><div class="tot-row final"><span>Total</span><span id="sqTotal">K0</span></div></div>
        <div style="margin-top:16px;font-size:12px"><strong>Lead time:</strong> <span id="sqLead"></span></div>
        <div style="margin-top:8px;font-size:12px"><strong>Notes:</strong> <span id="sqNotes"></span></div>
        <div style="margin-top:8px;font-size:12px;color:#555"><strong>Terms:</strong> <span id="sqTerms"></span></div>
      </div>
    </div>
  </div>

  <form id="saveForm" method="POST" style="display:none">@csrf<input type="hidden" name="_method" value="POST"><input type="hidden" name="payload" value=""></form>
</div>
@endsection
@push('scripts')
<script>window.quoteBuilderConfig = @json($config);</script>
@vite(['resources/js/quote-builder.js'])
<script>
(function(){
  const form=document.getElementById('saveForm');
  form.addEventListener('submit', function(e){
    // default submit used by JS after payload prepared
  });
  // Convert payload JSON into normal form fields for Laravel validation/old input simplicity
  const orig = document.getElementById('saveQuote');
  const _save = orig.onclick;
})();
</script>
@endpush
