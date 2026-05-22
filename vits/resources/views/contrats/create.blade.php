@extends('layouts.app')
@section('title', 'Nouveau contrat')
@section('content')
<a href="{{ route('contrats.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:1.5rem">Nouveau contrat</h1>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('contrats.store') }}">
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
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Créer le contrat</button>
        </div>
    </form>
</div>
@endsection
