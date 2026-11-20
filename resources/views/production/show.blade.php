@extends('layouts.app')
@section('title', $order->order_number)
@section('content')
@php
$badge = ['brief'=>'badge-muted','design'=>'badge-gold','production'=>'badge-navy','delivery'=>'badge-gold','completed'=>'badge-success','cancelled'=>'badge-error'][$order->stage] ?? 'badge-muted';
@endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">Module 02</div>
            <h1 class="page-title">🏭 {{ $order->order_number }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <span class="badge {{ $badge }}">{{ ucfirst($order->stage) }}</span>
                <span class="badge badge-muted">{{ ucfirst($order->vertical) }}</span>
                <span class="badge {{ $order->priority === 'urgent' ? 'badge-error' : 'badge-muted' }}">{{ ucfirst($order->priority) }}</span>
                <span style="font-size:13px;color:var(--text-muted);">{{ $order->product_type }} · {{ $order->client?->name }}</span>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('production.board') }}" class="btn-outline">📋 Board</a>
            <a href="{{ route('production.index') }}" class="btn-outline">← All Orders</a>
        </div>
    </div>
</div>
<div class="page-body">
    @if(session('success'))<div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error" style="margin-bottom:16px;">{{ session('error') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>@endif

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
        <div>
            <div class="card" style="padding:20px;margin-bottom:16px;text-align:center;">
                <div style="font-size:42px;font-weight:700;color:var(--navy);">{{ $order->stage_progress }}%</div>
                <div style="margin:12px 0;background:var(--border);border-radius:6px;height:8px;">
                    <div style="background:var(--gold);width:{{ $order->stage_progress }}%;height:8px;border-radius:6px;"></div>
                </div>
                <div style="font-size:12px;color:var(--text-muted);">Stage Progress</div>
            </div>

            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Details</div>
                @php $rows = [
                    'Client' => $order->client?->name ?? '—',
                    'Assigned To' => $order->assignedTo?->name ?? 'Unassigned',
                    'Invoice' => $order->invoice?->invoice_number ?? '—',
                    'Product' => $order->product_type,
                    'Quantity' => $order->quantity,
                    'Start Date' => optional($order->start_date)->format('d M Y') ?? '—',
                    'Due Date' => optional($order->due_date)->format('d M Y') ?? '—',
                    'Completed' => optional($order->completed_date)->format('d M Y') ?? '—',
                    'Quoted Price' => $order->quoted_price ? 'ZMW '.number_format($order->quoted_price,2) : '—',
                ]; @endphp
                @foreach($rows as $label => $value)
                <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-light);font-size:12px;">
                    <span style="color:var(--text-muted);width:85px;flex-shrink:0;">{{ $label }}</span><span>{{ $value }}</span>
                </div>
                @endforeach
                @if($order->brief)<p style="font-size:12px;color:var(--text-muted);margin-top:12px;"><strong>Brief:</strong> {{ $order->brief }}</p>@endif
                @if($order->internal_notes)<p style="font-size:12px;color:var(--text-muted);margin-top:8px;"><strong>Internal:</strong> {{ $order->internal_notes }}</p>@endif
            </div>

            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Update Stage</div>
                <form method="POST" action="{{ route('production.update-stage', $order) }}">
                    @csrf @method('PUT')
                    <div class="form-group"><label class="form-label">Stage</label>
                        <select name="stage" class="form-input">
                            @foreach(['brief','design','production','delivery','completed','cancelled'] as $option)
                            <option value="{{ $option }}" {{ $order->stage === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Progress (%)</label><input type="number" name="stage_progress" class="form-input" value="{{ $order->stage_progress }}" min="0" max="100"></div>
                    <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="2" placeholder="Reason for stage change…"></textarea></div>
                    <button type="submit" class="btn-gold" style="width:100%;font-size:12px;">Update Stage</button>
                </form>
            </div>

            <div class="card" style="padding:20px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">QC Checkpoint</div>
                <div style="margin-bottom:12px;">
                    @if($order->qc_passed === true)<span class="badge badge-success">Passed</span>
                    @elseif($order->qc_passed === false)<span class="badge badge-error">Failed</span>
                    @else<span class="badge badge-muted">Not checked</span>@endif
                </div>
                @if($order->qc_notes)<p style="font-size:12px;color:var(--text-muted);">{{ $order->qc_notes }}</p>@endif
                <form method="POST" action="{{ route('production.qc', $order) }}">
                    @csrf
                    <div class="form-group"><label class="form-label">Result</label>
                        <select name="qc_passed" class="form-input" required>
                            <option value="1" {{ $order->qc_passed === true ? 'selected' : '' }}>Passed</option>
                            <option value="0" {{ $order->qc_passed === false ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">QC Notes</label><textarea name="qc_notes" class="form-input" rows="2">{{ $order->qc_notes }}</textarea></div>
                    <button type="submit" class="btn-navy" style="width:100%;font-size:12px;">Save QC Check</button>
                </form>
            </div>

            <div class="card" style="padding:20px;">
                <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Assign Staff</div>
                <form method="POST" action="{{ route('production.assign', $order) }}">
                    @csrf @method('PUT')
                    <div class="form-group"><label class="form-label">Staff Member</label>
                        <select name="assigned_to" class="form-input">
                            <option value="">Unassigned</option>
                            @foreach($users as $u)<option value="{{ $u->id }}" {{ $order->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Priority</label>
                        <select name="priority" class="form-input">
                            @foreach(['low'=>'Low','normal'=>'Normal','high'=>'High','urgent'=>'Urgent'] as $value=>$label)
                            <option value="{{ $value }}" {{ $order->priority === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Internal Notes</label><textarea name="internal_notes" class="form-input" rows="2">{{ $order->internal_notes }}</textarea></div>
                    <button type="submit" class="btn-outline" style="width:100%;font-size:12px;">Save Assignment</button>
                </form>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom:16px;">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:600;color:var(--navy);">Specifications</div>
                <div style="padding:16px 20px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px;font-size:12px;">
                    <div>
                        <div style="font-weight:600;color:var(--navy);margin-bottom:6px;">Measurements</div>
                        @forelse($order->measurements ?? [] as $key => $value)
                        <div style="color:var(--text-muted);">{{ is_int($key) ? $value : $key.': '.$value }}</div>
                        @empty<div style="color:var(--text-muted);">—</div>@endforelse
                    </div>
                    <div>
                        <div style="font-weight:600;color:var(--navy);margin-bottom:6px;">Colour Specs</div>
                        @forelse($order->colour_specs ?? [] as $key => $value)
                        <div style="color:var(--text-muted);">{{ is_int($key) ? $value : $key.': '.$value }}</div>
                        @empty<div style="color:var(--text-muted);">—</div>@endforelse
                    </div>
                    <div>
                        <div style="font-weight:600;color:var(--navy);margin-bottom:6px;">Materials</div>
                        @forelse($order->fabric_requirements ?? [] as $material)
                        <div style="color:var(--text-muted);">{{ is_array($material) ? ($material['name'] ?? '—').' · '.($material['quantity'] ?? 0).' '.($material['unit'] ?? '') : $material }}</div>
                        @empty<div style="color:var(--text-muted);">—</div>@endforelse
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-size:13px;font-weight:600;color:var(--navy);">Stage History ({{ $order->stageLogs->count() }})</div>
                @forelse($order->stageLogs as $log)
                <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);font-size:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div style="color:var(--navy);font-weight:600;">{{ ucfirst($log->from_stage) }} → {{ ucfirst($log->to_stage) }}</div>
                        <div style="color:var(--text-muted);font-size:11px;">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }} · {{ $log->user?->name ?? 'System' }}</div>
                    </div>
                    @if($log->notes)<div style="color:var(--text-muted);margin-top:4px;">{{ $log->notes }}</div>@endif
                </div>
                @empty
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">No stage changes recorded yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
