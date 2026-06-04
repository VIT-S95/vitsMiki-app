@extends('layouts.app')
@section('title', 'Templates mail')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Templates mail</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">Modèles d'emails réutilisables</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="{{ route('parametres.index') }}" style="font-size:12px;color:#888;text-decoration:none;border:1px solid #ddd;padding:6px 12px;border-radius:8px">
            ← Paramètres
        </a>
        <a href="{{ route('mail-templates.create') }}" style="padding:8px 16px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border-radius:8px;text-decoration:none">
            + Nouveau template
        </a>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

@if($templates->isEmpty())
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:3rem;text-align:center;color:#aaa">
    <div style="font-size:32px;margin-bottom:0.75rem">✉️</div>
    <div style="font-size:14px;font-weight:500;margin-bottom:0.5rem">Aucun template</div>
    <div style="font-size:13px;margin-bottom:1.25rem">Créez votre premier modèle d'email.</div>
    <a href="{{ route('mail-templates.create') }}" style="padding:8px 20px;font-size:13px;background:#E8720C;color:#fff;border-radius:8px;text-decoration:none">
        Créer un template
    </a>
</div>
@else
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead>
            <tr style="background:#f9f9f9;border-bottom:1px solid #e0e0e0">
                <th style="padding:10px 16px;text-align:left;font-weight:500;color:#555">Nom</th>
                <th style="padding:10px 16px;text-align:left;font-weight:500;color:#555">Sujet</th>
                <th style="padding:10px 16px;text-align:left;font-weight:500;color:#555">Variables</th>
                <th style="padding:10px 16px;text-align:center;font-weight:500;color:#555">Statut</th>
                <th style="padding:10px 16px;text-align:right;font-weight:500;color:#555">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $template)
            <tr style="border-bottom:1px solid #f0f0f0">
                <td style="padding:10px 16px;font-weight:500;color:#1a1a1a">{{ $template->nom }}</td>
                <td style="padding:10px 16px;color:#555;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $template->sujet }}</td>
                <td style="padding:10px 16px;color:#888">
                    @if($template->variables)
                        <span style="font-size:11px">{{ implode(', ', $template->variables) }}</span>
                    @else
                        <span style="color:#ccc">—</span>
                    @endif
                </td>
                <td style="padding:10px 16px;text-align:center">
                    @if($template->actif)
                        <span style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;font-size:11px;padding:2px 8px;border-radius:20px">Actif</span>
                    @else
                        <span style="background:#f5f5f5;color:#aaa;border:1px solid #e0e0e0;font-size:11px;padding:2px 8px;border-radius:20px">Inactif</span>
                    @endif
                </td>
                <td style="padding:10px 16px;text-align:right;white-space:nowrap">
                    <a href="{{ route('mail-templates.edit', $template) }}" style="font-size:12px;color:#E8720C;text-decoration:none;border:1px solid #E8720C;padding:4px 10px;border-radius:6px;margin-right:6px">
                        Modifier
                    </a>
                    <form method="POST" action="{{ route('mail-templates.destroy', $template) }}" style="display:inline"
                          onsubmit="return confirm('Supprimer ce template ?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:12px;color:#dc2626;background:#fff;border:1px solid #fca5a5;padding:4px 10px;border-radius:6px;cursor:pointer">
                            Supprimer
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
