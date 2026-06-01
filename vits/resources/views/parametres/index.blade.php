@extends('layouts.app')
@section('title', 'Parametres')
@section('content')
<div style="margin-bottom:1.5rem">
    <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Parametres</h1>
    <p style="font-size:13px;color:#888;margin-top:2px">Configuration des seuils d alerte, Kizeo et motifs d intervention</p>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('parametres.update') }}" enctype="multipart/form-data">
@csrf
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:1rem;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div style="font-size:13px;font-weight:500">Alertes contrats</div>
        <div style="font-size:12px;color:#aaa">Seuils declenchant les alertes sur le tableau de bord</div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Alerte echeance contrat</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Delai avant la fin du contrat a partir duquel l alerte orange s affiche</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="seuil_echeance_mois" value="{{ $parametres['seuil_echeance_mois'] }}" min="1" max="12" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">mois</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Seuil depassement d heures</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Pourcentage des heures allouees consommees declenchant l alerte</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="seuil_heures_pct" value="{{ $parametres['seuil_heures_pct'] }}" min="50" max="100" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">%</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Expiration de session</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Durée d'inactivité avant déconnexion automatique (fermeture de l'app incluse)</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="session_minutes" value="{{ $parametres['session_minutes'] }}" min="5" max="480" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">min</span>
        </div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:1rem;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div style="font-size:13px;font-weight:500">Synchronisation Kizeo</div>
        <div style="font-size:12px;color:#aaa">Configuration de l import automatique des interventions</div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Frequence d import</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Intervalle entre deux synchronisations automatiques</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="kizeo_frequence_min" value="{{ $parametres['kizeo_frequence_min'] }}" min="5" max="120" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">min</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Cle API Kizeo</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Token d authentification pour l acces a l API Kizeo Forms</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="password" name="kizeo_api_key" value="{{ $parametres['kizeo_api_key'] }}" placeholder="kz_live_xxxx" id="api-key" style="width:200px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            <button type="button" onclick="document.getElementById('api-key').type=document.getElementById('api-key').type==='password'?'text':'password'" style="background:none;border:none;cursor:pointer;font-size:16px;color:#888">👁</button>
        </div>
    </div>
</div>

{{-- LOGO --}}
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:1rem;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div style="font-size:13px;font-weight:500">Logo VIT-S</div>
        <div style="font-size:12px;color:#aaa">Affiché dans la sidebar et sur les rapports PDF</div>
    </div>
    <div style="padding:12px 16px;display:flex;align-items:center;gap:16px">
        @if($logoPath)
            <img src="{{ $logoPath }}" style="height:48px;object-fit:contain;border:1px solid #eee;border-radius:6px;padding:4px">
            <div>
                <div style="font-size:12px;color:#166534;margin-bottom:6px">✓ Logo actuel</div>
                <button type="button" onclick="document.getElementById('form-delete-logo').submit()" style="font-size:11px;color:#dc2626;background:#fff;border:1px solid #fca5a5;padding:4px 10px;border-radius:6px;cursor:pointer">🗑 Supprimer</button>
            </div>
        @else
            <div style="width:80px;height:48px;border:2px dashed #ddd;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:11px">Aucun</div>
        @endif
        <div style="flex:1">
            <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">{{ $logoPath ? 'Remplacer le logo' : 'Ajouter un logo' }}</label>
            <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml" style="font-size:12px;color:#666">
            <div style="font-size:11px;color:#aaa;margin-top:3px">PNG, JPG ou SVG — max 2 Mo</div>
        </div>
    </div>
</div>


<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:1rem">
    <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">Enregistrer</button>
</div>
</form>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-size:13px;font-weight:500">Motifs d intervention</div>
            <div style="font-size:12px;color:#aaa">Liste des motifs proposes lors de la saisie manuelle</div>
        </div>
        <span style="font-size:11px;color:#aaa">{{ count($motifs) }} motif(s)</span>
    </div>
    <form method="POST" action="{{ route('parametres.motifs') }}">
        @csrf
        <div style="padding:12px 16px" id="motifs-list">
            @foreach($motifs as $motif)
            <div style="display:flex;gap:8px;margin-bottom:6px">
                <input type="text" name="motifs[]" value="{{ $motif }}" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                <button type="button" onclick="this.parentElement.remove()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#dc2626;cursor:pointer">X</button>
            </div>
            @endforeach
        </div>
        <div style="padding:0 16px 14px;display:flex;gap:8px">
            <input type="text" id="new-motif" placeholder="Nouveau motif..." style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            <button type="button" onclick="addMotif()" style="padding:7px 12px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer">+ Ajouter</button>
        </div>
        <div style="padding:10px 16px;border-top:1px solid #e0e0e0;display:flex;justify-content:flex-end">
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">Enregistrer les motifs</button>
        </div>
    </form>
</div>

<script>
function addMotif(){
    const val = document.getElementById('new-motif').value.trim();
    if (!val) return;
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:6px';
    div.innerHTML = '<input type="text" name="motifs[]" value="'+val+'" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px"><button type="button" onclick="this.parentElement.remove()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#dc2626;cursor:pointer">X</button>';
    document.getElementById('motifs-list').appendChild(div);
    document.getElementById('new-motif').value = '';
}
</script>
<form id="form-delete-logo" method="POST" action="{{ route('parametres.logo.delete') }}">
    @csrf @method('DELETE')
</form>
@endsection
