@extends('layouts.app')
@section('title', 'Modifier le contrat')
@section('content')
<a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:1.5rem">Modifier — {{ $contrat->client->nom_societe }}</h1>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('contrats.update', $contrat) }}">
        @csrf @method('PUT')
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Durée et dates</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date de début <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_debut" value="{{ old('date_debut', $contrat->date_debut?->format('Y-m-d')) }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée du contrat <span style="color:#E8720C">*</span></label>
                    <select name="duree_mois" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="12" {{ $contrat->duree_mois==12?'selected':'' }}>1 an</option>
                        <option value="24" {{ $contrat->duree_mois==24?'selected':'' }}>2 ans</option>
                        <option value="36" {{ $contrat->duree_mois==36?'selected':'' }}>3 ans</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée d'une période</label>
                    <select name="duree_periode_mois" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="12" {{ $contrat->duree_periode_mois==12?'selected':'' }}>12 mois</option>
                        <option value="1" {{ $contrat->duree_periode_mois==1?'selected':'' }}>1 mois</option>
                        <option value="3" {{ $contrat->duree_periode_mois==3?'selected':'' }}>3 mois</option>
                        <option value="6" {{ $contrat->duree_periode_mois==6?'selected':'' }}>6 mois</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Forfait</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Heures allouées / période <span style="color:#E8720C">*</span></label>
                    <input type="number" name="heures_par_periode" value="{{ old('heures_par_periode', $contrat->heures_par_periode) }}" min="1" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Statut</label>
                    <select name="statut" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="non-actif" {{ $contrat->statut=='non-actif'?'selected':'' }}>Non actif</option>
                        <option value="en-cours" {{ $contrat->statut=='en-cours'?'selected':'' }}>En cours</option>
                        <option value="expire" {{ $contrat->statut=='expire'?'selected':'' }}>Expiré</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <a href="{{ route('contrats.show', $contrat) }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Enregistrer</button>
        </div>
    </form>
</div>
@endsection
