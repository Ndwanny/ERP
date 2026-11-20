@extends('layouts.app')
@section('title', $campaign->name)
@section('content')
@php $statusColors=['draft'=>'badge-muted','scheduled'=>'badge-navy','active'=>'badge-success','paused'=>'badge-gold','completed'=>'badge-navy']; @endphp
<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-subtitle">Marketing</div>
            <h1 class="page-title">📢 {{ $campaign->name }}</h1>
            <div style="margin-top:6px;display:flex;gap:8px;">
                <span class="badge {{ $statusColors[$campaign->status]??'badge-muted' }}">{{ ucfirst($campaign->status) }}</span>
                <span class="badge badge-muted">{{ ucwords(str_replace('_',' ',$campaign->type)) }}</span>
            </div>
        </div>
        <a href="{{ route('marketing.campaigns') }}" class="btn-outline">← Campaigns</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">
    <div>
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.08em;">Campaign Info</div>
            @foreach(['Start'=>optional($campaign->start_date)->format('d M Y')??'—','End'=>optional($campaign->end_date)->format('d M Y')??'Ongoing','Budget'=>$campaign->budget?'ZMW '.number_format($campaign->budget,2):'—','Spent'=>$campaign->actual_spend?'ZMW '.number_format($campaign->actual_spend,2):'—','Leads'=>$campaign->leads->count().' captured'] as $l=>$v)
            <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border-light);font-size:13px;">
                <span style="color:var(--text-muted);width:70px;flex-shrink:0;">{{ $l }}</span><span>{{ $v }}</span>
            </div>
            @endforeach
            @if($campaign->description)<p style="font-size:12px;color:var(--text-muted);margin-top:12px;">{{ $campaign->description }}</p>@endif
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Update</div>
            <form method="POST" action="{{ route('marketing.campaigns.update', $campaign) }}">
                @csrf @method('PUT')
                <div class="form-group"><label class="form-label">Status</label>
                    <select name="status" class="form-input">@foreach(['draft','scheduled','active','paused','completed'] as $s)<option value="{{ $s }}" {{ old('status',$campaign->status)==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Budget (ZMW)</label><input type="number" name="budget" class="form-input" value="{{ $campaign->budget }}" min="0" step="0.01"></div>
                <div class="form-group"><label class="form-label">Actual Spend (ZMW)</label><input type="number" name="actual_spend" class="form-input" value="{{ $campaign->actual_spend }}" min="0" step="0.01"></div>
                <button type="submit" class="btn-navy" style="width:100%;font-size:12px;">Save</button>
            </form>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Leads ({{ $campaign->leads->count() }})</div>
            </div>
            @php $leadColors=['new'=>'badge-gold','contacted'=>'badge-navy','qualified'=>'badge-success','lost'=>'badge-error']; @endphp
            @forelse($campaign->leads as $lead)
            <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <div>
                    <div style="font-weight:500;color:var(--navy);">{{ $lead->name }}@if($lead->company) <span style="font-weight:400;color:var(--text-muted);">· {{ $lead->company }}</span>@endif</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $lead->source }}@if($lead->phone) · {{ $lead->phone }}@endif</div>
                </div>
                <span class="badge {{ $leadColors[$lead->status]??'badge-muted' }}" style="font-size:10px;">{{ ucfirst($lead->status) }}</span>
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No leads for this campaign</div>
            @endforelse
        </div>

        <div class="card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <div style="font-size:13px;font-weight:600;color:var(--navy);">Social Posts ({{ $campaign->posts->count() }})</div>
            </div>
            @forelse($campaign->posts as $post)
            <div style="padding:10px 20px;border-bottom:1px solid var(--border-light);font-size:13px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span class="badge badge-navy" style="font-size:10px;">{{ collect($post->platforms ?? [])->map(fn($p) => ucfirst($p))->implode(', ') ?: 'No platform' }}</span>
                    <span style="font-size:11px;color:var(--text-muted);">{{ optional($post->scheduled_at)->format('d M Y H:i')??'Not scheduled' }}</span>
                </div>
                <div style="font-size:12px;color:var(--text-muted);">{{ Str::limit($post->content,100) }}</div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:var(--text-muted);">No posts yet. Use the <a href="{{ route('marketing.social-scheduler') }}" style="color:var(--gold);">social scheduler</a>.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
