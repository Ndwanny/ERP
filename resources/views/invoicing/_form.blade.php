@php
$itemRows = old('items');
if ($itemRows === null) {
    $itemRows = $invoice
        ? $invoice->items->map(fn($i) => ['description'=>$i->description,'quantity'=>$i->quantity,'unit'=>$i->unit,'unit_price'=>$i->unit_price])->values()->all()
        : [['description'=>'','quantity'=>1,'unit'=>'pcs','unit_price'=>'']];
}
$selectedType = old('type', $type ?? ($invoice?->type ?? 'invoice'));
@endphp
<form method="POST" action="{{ $invoice ? route('invoicing.update', $invoice) : route('invoicing.store') }}">
    @csrf
    @if($invoice)@method('PUT')@endif

    <div class="card" style="padding:24px;margin-bottom:20px;">
        <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:20px;text-transform:uppercase;letter-spacing:0.1em;">Document Details</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group"><label class="form-label">Client *</label>
                <select name="client_id" class="form-input" required>
                    <option value="">Select client…</option>
                    @foreach($clients as $c)<option value="{{ $c->id }}" {{ old('client_id', $invoice?->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}{{ $c->company ? ' ('.$c->company.')' : '' }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Document Type *</label>
                <select name="type" class="form-input" required>
                    @foreach(['quote'=>'Quote','proforma'=>'Proforma Invoice','invoice'=>'Invoice','receipt'=>'Receipt','credit_note'=>'Credit Note'] as $value=>$label)
                    <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Vertical</label>
                <select name="vertical" class="form-input">
                    <option value="">—</option>
                    @foreach(['bags'=>'Bags','furniture'=>'Furniture','interiors'=>'Interiors','mixed'=>'Mixed'] as $value=>$label)
                    <option value="{{ $value }}" {{ old('vertical', $invoice?->vertical) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Currency</label><input type="text" name="currency" class="form-input" value="{{ old('currency', $invoice?->currency ?? 'ZMW') }}" maxlength="10"></div>
            <div class="form-group"><label class="form-label">Issue Date *</label><input type="date" name="issue_date" class="form-input" value="{{ old('issue_date', $invoice?->issue_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required></div>
            <div class="form-group"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-input" value="{{ old('due_date', $invoice?->due_date?->format('Y-m-d') ?? date('Y-m-d', strtotime('+14 days'))) }}"></div>
            <div class="form-group"><label class="form-label">Discount (%)</label><input type="number" name="discount_percent" class="form-input" value="{{ old('discount_percent', $invoice?->discount_percent ?? 0) }}" min="0" max="100" step="0.01"></div>
            <div class="form-group"><label class="form-label">VAT (%)</label><input type="number" name="tax_percent" class="form-input" value="{{ old('tax_percent', $invoice?->tax_percent ?? 16) }}" min="0" max="100" step="0.01"></div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="2">{{ old('notes', $invoice?->notes) }}</textarea></div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Terms</label><textarea name="terms" class="form-input" rows="2">{{ old('terms', $invoice?->terms) }}</textarea></div>
        </div>
    </div>

    <div class="card" style="padding:24px;margin-bottom:20px;">
        <h3 style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:6px;text-transform:uppercase;letter-spacing:0.1em;">Line Items</h3>
        <p style="font-size:11px;color:var(--text-muted);margin-bottom:18px;">Subtotal, discount, VAT (16%) and total are calculated automatically.</p>
        <div id="items-container">
            @foreach($itemRows as $index => $row)
            <div class="item-row" style="display:grid;grid-template-columns:3fr 0.7fr 0.8fr 1fr 40px;gap:10px;align-items:end;margin-bottom:10px;">
                <div class="form-group" style="margin:0;"><label class="form-label">Description</label><input type="text" name="items[{{ $index }}][description]" class="form-input" value="{{ old('items.'.$index.'.description', $row['description'] ?? '') }}" required placeholder="Item or service"></div>
                <div class="form-group" style="margin:0;"><label class="form-label">Qty</label><input type="number" name="items[{{ $index }}][quantity]" class="form-input" value="{{ old('items.'.$index.'.quantity', $row['quantity'] ?? 1) }}" min="1" required></div>
                <div class="form-group" style="margin:0;"><label class="form-label">Unit</label><input type="text" name="items[{{ $index }}][unit]" class="form-input" value="{{ old('items.'.$index.'.unit', $row['unit'] ?? 'pcs') }}"></div>
                <div class="form-group" style="margin:0;"><label class="form-label">Unit Price</label><input type="number" name="items[{{ $index }}][unit_price]" class="form-input" value="{{ old('items.'.$index.'.unit_price', $row['unit_price'] ?? '') }}" min="0" step="0.01" required></div>
                <button type="button" class="btn-outline remove-item" style="padding:9px 0;font-size:16px;" title="Remove row">×</button>
            </div>
            @endforeach
        </div>
        <button type="button" id="add-item" class="btn-outline" style="font-size:12px;">+ Add Line Item</button>
    </div>

    <div style="display:flex;gap:12px;">
        <button type="submit" class="btn-gold">{{ $invoice ? '✓ Save Changes' : '✓ Create ' . ucfirst($selectedType) }}</button>
        <a href="{{ route('invoicing.index') }}" class="btn-outline">Cancel</a>
    </div>
</form>

<template id="item-template">
    <div class="item-row" style="display:grid;grid-template-columns:3fr 0.7fr 0.8fr 1fr 40px;gap:10px;align-items:end;margin-bottom:10px;">
        <div class="form-group" style="margin:0;"><label class="form-label">Description</label><input type="text" name="items[__INDEX__][description]" class="form-input" required placeholder="Item or service"></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Qty</label><input type="number" name="items[__INDEX__][quantity]" class="form-input" value="1" min="1" required></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Unit</label><input type="text" name="items[__INDEX__][unit]" class="form-input" value="pcs"></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Unit Price</label><input type="number" name="items[__INDEX__][unit_price]" class="form-input" value="0" min="0" step="0.01" required></div>
        <button type="button" class="btn-outline remove-item" style="padding:9px 0;font-size:16px;" title="Remove row">×</button>
    </div>
</template>

<script>
(function () {
    var container = document.getElementById('items-container');
    var template = document.getElementById('item-template');
    var index = container.querySelectorAll('.item-row').length;
    document.getElementById('add-item').addEventListener('click', function () {
        container.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, index++));
    });
    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-item') && container.querySelectorAll('.item-row').length > 1) {
            event.target.closest('.item-row').remove();
        }
    });
})();
</script>
