@extends('layouts.app')
@section('title', $user ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')
@section('content')
<div style="margin-bottom:1.25rem">
    <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">{{ $user ? 'Modifier ' . $user->name : 'Nouvel utilisateur' }}</h1>
</div>

@if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:1rem">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif

<form method="POST" action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}"
      style="max-width:520px">
    @csrf
    @if($user) @method('PUT') @endif

    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem;margin-bottom:1rem">
        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:12px;color:#666;margin-bottom:5px">Nom</label>
            <input type="text" name="name" value="{{ old('name', $user?->name) }}" required
                style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        </div>
        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:12px;color:#666;margin-bottom:5px">Email</label>
            <input type="email" name="email" value="{{ old('email', $user?->email) }}" required
                style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        </div>
        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:12px;color:#666;margin-bottom:5px">
                Mot de passe {{ $user ? '(laisser vide pour ne pas changer)' : '' }}
            </label>
            <input type="password" name="password" {{ $user ? '' : 'required' }}
                style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        </div>
    </div>

    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem;margin-bottom:1rem">
        <div style="font-size:12px;font-weight:500;color:#1a1a1a;margin-bottom:10px">Droits d'accès</div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:10px;cursor:pointer">
            <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user?->is_admin) ? 'checked' : '' }}
                style="width:14px;height:14px">
            Administrateur (accès complet)
        </label>

        <div style="font-size:12px;color:#666;margin-bottom:6px;margin-top:4px">Thèmes</div>
        @foreach($themes as $t)
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="checkbox" name="themes[]" value="{{ $t }}"
                {{ in_array($t, old('themes', $user?->themes ?? [])) ? 'checked' : '' }}
                style="width:14px;height:14px">
            {{ ucfirst($t) }}
        </label>
        @endforeach
    </div>

    <div style="display:flex;gap:8px">
        <button type="submit" style="padding:8px 20px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer">
            {{ $user ? 'Enregistrer' : 'Créer' }}
        </button>
        <a href="{{ route('admin.users.index') }}" style="padding:8px 16px;background:#f5f5f5;border:1px solid #ddd;color:#555;border-radius:8px;font-size:13px;text-decoration:none">
            Annuler
        </a>
    </div>
</form>
@endsection
