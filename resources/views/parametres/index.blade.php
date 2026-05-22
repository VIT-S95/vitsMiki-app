@extends('layouts.app')
@section('title', 'Paramètres')
@section('content')
<div style="margin-bottom:1.5rem">
    <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Paramètres</h1>
    <p style="font-size:13px;color:#888;margin-top:2px">Configuration des seuils d'alerte, Kizeo et motifs d'intervention</p>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('parametres.update') }}">
@csrf

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:1rem;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;gap:8px">
        <div style="width:28px;height:28px;border-radius:8px;background:#FFF3E6;display:flex;align-items:center;justify-content:center">📋</div>
        <div>
            <div style="font-size:13px;font-weight:500">Alertes contrats</div>
            <div style="font-size:12px;color:#aaa">Seuils déclenchant les alertes sur le tableau de bord</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Alerte échéance contrat</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Délai avant la fin du contrat à partir duquel l'alerte orange s'affiche</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="seuil_echeance_mois" value="{{ $parametres['seuil_echeance_mois'] }}" min="1" max="12" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">mois</span>
            <span style="background:#FFF3E6;color:#E8720C;padding:3px 8px;border-radius:99px;font-size:12px">{{ $parametres['seuil_echeance_mois'] }} mois</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Seuil dépassement d'heures</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Pourcentage des heures allouées consommées déclenchant l'alerte</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="seuil_heures_pct" value="{{ $parametres['seuil_heures_pct'] }}" min="50" max="100" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">%</span>
            <span style="background:#FFF3E6;color:#E8720C;padding:3px 8px;border-radius:99px;font-size:12px">{{ $parametres['seuil_heures_pct'] }}%</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Expiration de session</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Durée d'inactivité avant déconnexion automatique</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="session_heures" value="{{ $parametres['session_heures'] }}" min="1" max="24" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">heures</span>
            <span style="background:#f0fdf4;color:#166534;padding:3px 8px;border-radius:99px;font-size:12px">{{ $parametres['session_heures'] }}h</span>
        </div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:1rem;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;gap:8px">
        <div style="width:28px;height:28px;border-radius:8px;background:#FFF3E6;display:flex;align-items:center;justify-content:center">☁</div>
        <div>
            <div style="font-size:13px;font-weight:500">Synchronisation Kizeo</div>
            <div style="font-size:12px;color:#aaa">Configuration de l'import automatique des interventions</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Fréquence d'import</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Intervalle entre deux synchronisations automatiques</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="number" name="kizeo_frequence_min" value="{{ $parametres['kizeo_frequence_min'] }}" min="5" max="120" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            <span style="font-size:12px;color:#888">min</span>
            <span style="background:#f0fdf4;color:#166534;padding:3px 8px;border-radius:99px;font-size:12px">{{ $parametres['kizeo_frequence_min'] }} min</span>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px">
        <div>
            <div style="font-size:13px;color:#1a1a1a">Clé API Kizeo</div>
            <div style="font-size:12px;color:#aaa;margin-top:2px">Token d'authentification pour l'accès à l'API Kizeo Forms</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <input type="password" name="kizeo_api_key" value="{{ $parametres['kizeo_api_key'] }}" placeholder="kz_live_xxxx" id="api-key" style="width:200px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            <button type="button" onclick="toggleKey()" style="background:none;border:none;cursor:pointer;font-size:16px;color:#888">👁</button>
        </div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:1rem">
    <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Enregistrer</button>
</div>
</form>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#FFF3E6;display:flex;align-items:center;justify-content:center">🏷</div>
            <div>
                <div style="font-size:13px;font-weight:500">Motifs d'intervention</div>
                <div style="font-size:12px;color:#aaa">Liste des motifs proposés lors de la saisie manuelle</div>
            </div>
        </div>
        <span style="font-size:11px;color:#aaa">{{ count($motifs) }} motif(s)</span>
    </div>
    <form method="POST" action="{{ route('parametres.motifs') }}">
        @csrf
        <div style="padding:12px 16px" id="motifs-list">
            @foreach($motifs as $i => $motif)
            <div style="display:flex;gap:8px;margin-bottom:6px">
                <input type="text" name="motifs[]" value="{{ $motif }}" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                <button type="button" onclick="this.parentElement.remove()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#dc2626;cursor:pointer">🗑</button>
            </div>
            @endforeach
        </div>
        <div style="padding:0 16px 14px">
            <div style="display:flex;gap:8px">
                <input type="text" id="new-motif" placeholder="Nouveau motif…" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                <button type="button" onclick="addMotif()" style="padding:7px 12px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer">+ Ajouter</button>
            </div>
        </div>
        <div style="padding:10px 16px;border-top:1px solid #e0e0e0;display:flex;justify-content:flex-end">
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Enregistrer les motifs</button>
        </div>
    </form>
</div>

<script>
function toggleKey(){
    const inp = document.getElementById('api-key');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
function addMotif(){
    const val = document.getElementById('new-motif').value.trim();
    if (!val) return;
    const div = document.createElement('div');
    div.style = 'display:flex;gap:8px;margin-bottom:6px';
    div.innerHTML = '<input type="text" name="motifs[]" value="'+val+'" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px"><button type="button" onclick="this.parentElement.remove()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#dc2626;cursor:pointer">🗑</button>';
    document.getElementById('motifs-list').appendChild(div);
    document.getElementById('new-motif').value = '';
}
</script>
@endsection
