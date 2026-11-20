@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
<div class="page-header">
    <div class="page-header-inner">
        <div><div class="page-subtitle">Admin</div><h1 class="page-title">🛡️ Roles & Permissions</h1></div>
        <a href="{{ route('admin.index') }}" class="btn-outline">← Admin</a>
    </div>
</div>

@if(session('success'))<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-success">{{ session('success') }}</div></div>@endif
@if($errors->any())<div style="padding:0 24px;margin-top:16px;"><div class="alert alert-error">{{ $errors->first() }}</div></div>@endif

<div class="page-body" style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">

    <div class="card" style="padding:20px;">
        <div style="font-size:12px;font-weight:600;color:var(--navy);margin-bottom:14px;text-transform:uppercase;letter-spacing:0.08em;">Add Role</div>
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Role Slug *</label><input type="text" name="name" class="form-input" required placeholder="e.g. accounts-manager" style="font-family:monospace;"></div>
            <div class="form-group"><label class="form-label">Display Name *</label><input type="text" name="display_name" class="form-input" required placeholder="e.g. Accounts Manager"></div>
            <button type="submit" class="btn-gold" style="width:100%;">+ Add Role</button>
        </form>
    </div>

    <div>
        @foreach($roles as $role)
        <div class="card" style="margin-bottom:16px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--navy);">{{ $role->display_name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);font-family:monospace;">{{ $role->name }}</div>
                </div>
                @if($role->name === 'super-admin')<span class="badge badge-gold">System Role</span>@endif
            </div>
            @if($role->name !== 'super-admin')
            <form method="POST" action="{{ route('admin.roles.permissions', $role) }}" style="padding:16px 20px;">
                @csrf @method('PUT')
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:16px;">
                    @foreach($all_permissions as $perm)
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;cursor:pointer;padding:6px;border:1px solid var(--border-light);border-radius:6px;{{ in_array($perm, (array)$role->permissions)?'background:rgba(249,176,64,0.08);border-color:var(--gold);':'' }}">
                        <input type="checkbox" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, (array)$role->permissions)?'checked':'' }}>
                        {{ str_replace('access-','',str_replace('-',' ',$perm)) }}
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="btn-navy" style="font-size:12px;">Save Permissions</button>
            </form>
            @else
            <div style="padding:16px 20px;font-size:12px;color:var(--text-muted);">Super Admin has unrestricted access to all modules — permissions not configurable.</div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
