@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Utilisateurs</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $users->count() }} utilisateur(s)</p>
    </div>
    <a href="{{ route('admin.users.create') }}" style="padding:8px 16px;background:#E8720C;color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none">+ Nouvel utilisateur</a>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('error') }}</div>
@endif

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead style="background:#f5f5f5">
            <tr>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Nom</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Email</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Admin</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Thèmes</th>
                <th style="padding:9px 14px;border-bottom:1px solid #e0e0e0"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr style="border-bottom:1px solid #f0f0f0">
                <td style="padding:10px 14px;font-weight:500">
                    {{ $u->name }}
                    @if($u->id === auth()->id())
                        <span style="font-size:10px;color:#aaa;margin-left:4px">(vous)</span>
                    @endif
                </td>
                <td style="padding:10px 14px;color:#555">{{ $u->email }}</td>
                <td style="padding:10px 14px">
                    @if($u->is_admin)
                        <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:4px;font-size:11px">Admin</span>
                    @else
                        <span style="color:#aaa;font-size:12px">—</span>
                    @endif
                </td>
                <td style="padding:10px 14px">
                    @forelse($u->themes ?? [] as $t)
                        <span style="background:#f0f0f0;color:#555;padding:2px 7px;border-radius:4px;font-size:11px;margin-right:3px">{{ $t }}</span>
                    @empty
                        <span style="color:#aaa;font-size:12px">—</span>
                    @endforelse
                </td>
                <td style="padding:10px 14px;text-align:right;display:flex;gap:6px;justify-content:flex-end">
                    <a href="{{ route('admin.users.edit', $u) }}" style="font-size:12px;color:#555;text-decoration:none;padding:4px 10px;border:1px solid #ddd;border-radius:6px">Modifier</a>
                    @if($u->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Supprimer {{ $u->name }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:12px;color:#dc2626;background:#fff;border:1px solid #fca5a5;padding:4px 10px;border-radius:6px;cursor:pointer">Supprimer</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:2rem;text-align:center;color:#aaa">Aucun utilisateur</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
