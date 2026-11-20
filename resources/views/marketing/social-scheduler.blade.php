@extends('layouts.app')
@section('title', 'Social Scheduler')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Marketing</div><h1 class="page-title">📱 Social Media Scheduler</h1></div>
        <a href="{{ route('marketing.index') }}" class="btn-outline">← Marketing</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

@php $platforms=['instagram'=>'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok','whatsapp'=>'WhatsApp Business','twitter'=>'X (Twitter)','linkedin'=>'LinkedIn']; @endphp
<div class="page-body" style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;">
    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Schedule Post</div>
        <form method="POST" action="{{ route('marketing.social-posts.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Platforms * (select one or more)</label>
                <select name="platforms[]" class="form-input" multiple size="5" required>@foreach($platforms as $v=>$l)<option value="{{ $v }}" {{ in_array($v, old('platforms', []))?'selected':'' }}>{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Campaign</label>
                <select name="campaign_id" class="form-input"><option value="">No campaign</option>@foreach($campaigns as $c)<option value="{{ $c->id }}" {{ old('campaign_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
            </div>
            <div class="form-group"><label class="form-label">Content *</label><textarea name="content" class="form-input" rows="5" required placeholder="Write your post caption…">{{ old('content') }}</textarea></div>
            <div class="form-group"><label class="form-label">Schedule Date & Time</label><input type="datetime-local" name="scheduled_at" class="form-input" value="{{ old('scheduled_at') }}"></div>
            <div class="form-group"><label class="form-label">Status *</label>
                <select name="status" class="form-input">@foreach(['draft'=>'Draft','scheduled'=>'Scheduled','published'=>'Published','failed'=>'Failed'] as $v=>$l)<option value="{{ $v }}" {{ old('status','draft')==$v?'selected':'' }}>{{ $l }}</option>@endforeach</select>
            </div>
            <button type="submit" class="btn-gold" style="width:100%;">📅 Schedule Post</button>
        </form>
    </div>

    <div class="card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <div style="font-size:13px;font-weight:600;color:var(--navy);">Scheduled & Recent Posts</div>
        </div>
        @php $platformIcons=['facebook'=>'📘','instagram'=>'📸','tiktok'=>'🎵','whatsapp'=>'💬','twitter'=>'🐦','linkedin'=>'💼']; @endphp
        @php $statusColors=['draft'=>'badge-muted','scheduled'=>'badge-gold','published'=>'badge-success','failed'=>'badge-error']; @endphp
        @forelse($posts as $post)
        <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);font-size:13px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span>{{ $platformIcons[$post->platforms[0]??'']??'📱' }}</span>
                    <strong style="color:var(--navy);">{{ collect($post->platforms??[])->map(fn($p)=>ucfirst($p))->implode(', ')?:'No platform' }}</strong>
                    @if($post->campaign)<span style="font-size:11px;color:var(--text-muted);">· {{ $post->campaign->name }}</span>@endif
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="badge {{ $statusColors[$post->status]??'badge-muted' }}" style="font-size:10px;">{{ ucfirst($post->status) }}</span>
                    @if($post->scheduled_at)<span style="font-size:11px;color:var(--text-muted);">{{ $post->scheduled_at->format('d M Y H:i') }}</span>@endif
                </div>
            </div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $post->content }}</div>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted);">No posts yet. Write your first one using the form.</div>
        @endforelse
        @if($posts->hasPages())<div style="padding:16px 20px;">{{ $posts->links() }}</div>@endif
    </div>
</div>
@endsection
