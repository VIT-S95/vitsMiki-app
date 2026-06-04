@extends('layouts.app')
@section('title', $template->exists ? 'Modifier le template' : 'Nouveau template')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">
            {{ $template->exists ? 'Modifier le template' : 'Nouveau template mail' }}
        </h1>
        <p style="font-size:13px;color:#888;margin-top:2px">
            {{ $template->exists ? $template->nom : 'Définir un modèle d\'email réutilisable' }}
        </p>
    </div>
    <a href="{{ route('mail-templates.index') }}" style="font-size:12px;color:#888;text-decoration:none;border:1px solid #ddd;padding:6px 12px;border-radius:8px">
        ← Retour
    </a>
</div>

@if($errors->any())
<div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">
    <ul style="margin:0;padding-left:1rem">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $template->exists ? route('mail-templates.update', $template) : route('mail-templates.store') }}">
@csrf
@if($template->exists) @method('PUT') @endif

<div style="display:grid;grid-template-columns:1fr 280px;gap:1rem;align-items:start">
    {{-- Formulaire principal --}}
    <div>
        <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden;margin-bottom:1rem">
            <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0">
                <div style="font-size:13px;font-weight:500">Informations du template</div>
            </div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:14px">
                <div>
                    <label style="font-size:12px;color:#555;display:block;margin-bottom:5px;font-weight:500">Nom du template *</label>
                    <input type="text" name="nom" value="{{ old('nom', $template->nom) }}"
                           placeholder="Ex: Rapport mensuel client"
                           style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#555;display:block;margin-bottom:5px;font-weight:500">Sujet de l'email *</label>
                    <input type="text" name="sujet" value="{{ old('sujet', $template->sujet) }}"
                           placeholder="Ex: Rapport de maintenance — {{client_nom}}"
                           style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <div style="font-size:11px;color:#aaa;margin-top:4px">Vous pouvez utiliser des variables entre accolades doubles : <code>{{"{{"}}variable{{"}}"}}</code></div>
                </div>
                <div>
                    <label style="font-size:12px;color:#555;display:block;margin-bottom:5px;font-weight:500">Corps de l'email *</label>
                    <textarea name="corps" rows="14"
                              placeholder="Bonjour {{"{{"}}client_nom{{"}}"}},&#10;&#10;Veuillez trouver ci-joint votre rapport de maintenance pour la période du {{"{{"}}periode_debut{{"}}"}} au {{"{{"}}periode_fin{{"}}"}}.&#10;&#10;{{"{{"}}signature{{"}}"}}"
                              style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical;font-family:Arial,sans-serif;line-height:1.5">{{ old('corps', $template->corps) }}</textarea>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    <input type="checkbox" name="actif" id="actif" value="1"
                           {{ old('actif', $template->actif ?? true) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:#E8720C">
                    <label for="actif" style="font-size:13px;color:#555;cursor:pointer">Template actif</label>
                </div>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden;margin-bottom:1rem">
            <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0">
                <div style="font-size:13px;font-weight:500">Variables disponibles</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">Une variable par ligne — utilisées pour documenter les placeholders du template</div>
            </div>
            <div style="padding:16px">
                <textarea name="variables" rows="5"
                          placeholder="client_nom&#10;periode_debut&#10;periode_fin&#10;heures_consommees&#10;signature"
                          style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical;font-family:monospace">{{ old('variables', $template->variables ? implode("\n", $template->variables) : '') }}</textarea>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px">
            <a href="{{ route('mail-templates.index') }}"
               style="padding:8px 20px;font-size:13px;background:#fff;border:1px solid #ddd;border-radius:8px;color:#555;text-decoration:none">
                Annuler
            </a>
            <button type="submit"
                    style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">
                {{ $template->exists ? 'Enregistrer les modifications' : 'Créer le template' }}
            </button>
        </div>
    </div>

    {{-- Panneau aide variables --}}
    <div>
        <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden;position:sticky;top:1rem">
            <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0">
                <div style="font-size:13px;font-weight:500">Variables suggérées</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">Cliquer pour insérer dans le corps</div>
            </div>
            <div style="padding:12px 16px;display:flex;flex-direction:column;gap:6px">
                @php
                $suggestions = [
                    ['client_nom',          'Nom du client'],
                    ['client_email',        'Email du client'],
                    ['contrat_numero',      'Numéro de contrat'],
                    ['periode_debut',       'Début de période'],
                    ['periode_fin',         'Fin de période'],
                    ['heures_consommees',   'Heures consommées'],
                    ['heures_allouees',     'Heures allouées'],
                    ['technicien_nom',      'Nom du technicien'],
                    ['date_intervention',   'Date d\'intervention'],
                    ['signature',           'Signature email'],
                ];
                @endphp
                @foreach($suggestions as [$var, $desc])
                <button type="button"
                        onclick="insertVar('{{'{{'}}{{ $var }}{{'}}'}}' )"
                        title="{{ $desc }}"
                        style="text-align:left;padding:6px 10px;border:1px solid #e0e0e0;border-radius:6px;background:#f9f9f9;cursor:pointer;font-size:12px;color:#555;display:flex;justify-content:space-between;align-items:center">
                    <code style="color:#E8720C;font-size:11px">{{"{{"}}{{ $var }}{{"}}"}}</code>
                    <span style="color:#aaa;font-size:11px">{{ $desc }}</span>
                </button>
                @endforeach
            </div>
            <div style="padding:10px 16px;border-top:1px solid #e0e0e0;font-size:11px;color:#aaa">
                Les variables sont remplacées au moment de l'envoi.
            </div>
        </div>
    </div>
</div>
</form>

<script>
function insertVar(text) {
    const ta = document.querySelector('textarea[name="corps"]');
    const start = ta.selectionStart, end = ta.selectionEnd;
    ta.value = ta.value.substring(0, start) + text + ta.value.substring(end);
    ta.selectionStart = ta.selectionEnd = start + text.length;
    ta.focus();
}
</script>
@endsection
