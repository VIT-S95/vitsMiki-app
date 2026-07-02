@extends('layouts.app')
@section('title', 'Nouveau contrat')
@section('content')
@if(session('alerte_contrat_existant'))
@php $alerte = session('alerte_contrat_existant'); @endphp
<div id="modal-alerte-contrat" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:9999">
    <div style="background:#fff;border-radius:12px;padding:1.5rem;max-width:480px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.18)">
        <div style="font-size:15px;font-weight:500;color:#1a1a1a;margin-bottom:0.75rem">⚠️ Contrat actif existant</div>
        <div style="font-size:13px;color:#555;background:#fff8f0;border:1px solid #fddcbf;border-radius:8px;padding:10px 12px;margin-bottom:1rem;line-height:1.7">
            Ce client a déjà un contrat en cours :<br>
            <strong>{{ $alerte['numero'] }}</strong> — du {{ $alerte['date_debut'] }} au {{ $alerte['date_fin'] }}
        </div>
        <div style="font-size:13px;color:#555;margin-bottom:1.25rem">Que souhaitez-vous faire ?</div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <button onclick="document.getElementById('modal-alerte-contrat').remove()"
                style="padding:9px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;background:#fff;cursor:pointer;text-align:left">
                ✖ Annuler — c'était une erreur de saisie
            </button>
            <button onclick="confirmerNouveauContrat()"
                style="padding:9px 16px;font-size:13px;border:1px solid #E8720C;border-radius:8px;color:#E8720C;background:#fff8f3;cursor:pointer;text-align:left">
                ✓ Nouveau contrat négocié — expirer l'ancien et basculer les interventions
            </button>
        </div>
    </div>
</div>
<script>
function confirmerNouveauContrat() {
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'confirme_nouveau_contrat';
    input.value = '1';
    document.querySelector('form').appendChild(input);
    document.querySelector('form').submit();
}
</script>
@endif
<a href="{{ route('contrats.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:1.5rem">Nouveau contrat</h1>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form id="form-contrat" method="POST" action="{{ route('contrats.store') }}">
        @csrf
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Client</div>
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Client (sans contrat actif) <span style="color:#E8720C">*</span></label>
                <select name="client_id" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <option value="">— Sélectionner un client —</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_societe }}</option>
                    @endforeach
                </select>
                <div style="font-size:11px;color:#aaa;margin-top:3px">Seuls les clients sans contrat en cours sont listés</div>
                @error('client_id')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° contrat VIT-S <span style="color:#E8720C">*</span></label>
                <input type="text" name="numero_contrat_vits" value="{{ old('numero_contrat_vits') }}" required placeholder="ex: CLORELICE/RZA1234/2025" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                @error('numero_contrat_vits')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Durée et dates</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date de début <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_debut" value="{{ old('date_debut') }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    @error('date_debut')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée du contrat <span style="color:#E8720C">*</span></label>
                    <select name="duree_mois" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">—</option>
                        <option value="12" {{ old('duree_mois')==12?'selected':'' }}>1 an</option>
                        <option value="24" {{ old('duree_mois')==24?'selected':'' }}>2 ans</option>
                        <option value="36" {{ old('duree_mois')==36?'selected':'' }}>3 ans</option>
                    </select>
                    @error('duree_mois')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée d'une période <span style="color:#E8720C">*</span></label>
                    <select name="duree_periode_mois" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="12" selected>12 mois</option>
                        <option value="1">1 mois</option>
                        <option value="3">3 mois</option>
                        <option value="6">6 mois</option>
                    </select>
                    <div style="font-size:11px;color:#aaa;margin-top:3px">12 mois par défaut</div>
                </div>
            </div>
        </div>

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Forfait</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Heures allouées / période <span style="color:#E8720C">*</span></label>
                    <input type="number" name="heures_par_periode" value="{{ old('heures_par_periode', 10) }}" min="1" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    @error('heures_par_periode')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Statut initial</label>
                    <select name="statut" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="non-actif">Non actif</option>
                        <option value="en-cours">En cours</option>
                    </select>
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Non actif = à valider avant activation</div>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <a href="{{ route('contrats.index') }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
            <button type="button" onclick="document.getElementById('form-contrat').submit()" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Créer le contrat</button>
        </div>
    </form>
</div>
@endsection
