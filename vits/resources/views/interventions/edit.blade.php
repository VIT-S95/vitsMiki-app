@extends('layouts.app')
@section('title', 'Modifier intervention')
@section('content')
@if($intervention->contrat_id)
<a href="{{ route('contrats.show', $intervention->contrat_id) }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour au contrat — {{ $intervention->contrat?->client?->nom_societe }}</a>
@else
<a href="{{ route('interventions.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour aux interventions</a>
@endif
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:4px">Modifier l'intervention</h1>
<p style="font-size:13px;color:#888;margin-bottom:1.5rem">Bon n° {{ $intervention->numero_bon_kizeo ?? $intervention->id }} — {{ $intervention->date_intervention->format('d/m/Y') }}</p>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('interventions.update', $intervention) }}" id="form-intervention">
        @csrf
        @method('PUT')

        {{-- IDENTIFICATION --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_intervention" value="{{ old('date_intervention', $intervention->date_intervention->format('Y-m-d')) }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Heure</label>
                    <input type="time" name="heure_intervention" value="{{ old('heure_intervention', $intervention->heure_intervention) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Technicien</label>
                    <input type="text" name="technicien" value="{{ old('technicien', $intervention->technicien) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Client</label>
                    <input type="text" name="client_nom" value="{{ old('client_nom', $intervention->client_nom) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Contrat</label>
                    <select name="contrat_id" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Hors contrat —</option>
                        @foreach($contratsActifs as $c)
                        <option value="{{ $c->id }}" {{ (old('contrat_id', $intervention->contrat_id) == $c->id) ? 'selected' : '' }}>
                            {{ $c->client->nom_societe }} — {{ $c->numero_contrat_vits ?? 'N/A' }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- TYPE + DURÉE --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Type d'intervention</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Type <span style="color:#E8720C">*</span></label>
                    <select name="type" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        @php $typeVal = old('type', $intervention->type); @endphp
                        <option value="site"       {{ $typeVal=='site'?'selected':'' }}>Sur site</option>
                        <option value="distance"   {{ $typeVal=='distance'?'selected':'' }}>À distance</option>
                        <option value="flash"      {{ $typeVal=='flash'?'selected':'' }}>Flash</option>
                        <option value="ajustement" {{ $typeVal=='ajustement'?'selected':'' }}>Ajustement</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée (minutes) <span style="color:#E8720C">*</span></label>
                    <input type="number" name="duree_minutes" min="-999" max="999" step="1" value="{{ old('duree_minutes', $intervention->duree_minutes) }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>
            <div style="display:flex;gap:1.5rem;margin-top:1rem">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#444">
                    <input type="checkbox" name="deductible" value="1" {{ old('deductible', $intervention->deductible) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#E8720C">
                    Déductible du forfait contrat
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#444">
                    <input type="checkbox" name="hors_heure_ouvree" value="1" {{ old('hors_heure_ouvree', $intervention->hors_heure_ouvree) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#E8720C">
                    Hors heure ouvrée
                </label>
            </div>
        </div>

        {{-- DEMANDE --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Demande</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Donneur d'ordre</label>
                    <input type="text" name="donneur_ordre" value="{{ old('donneur_ordre', $intervention->donneur_ordre) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° ticket</label>
                    <input type="text" name="n_ticket" value="{{ old('n_ticket', $intervention->n_ticket) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° devis</label>
                    <input type="text" name="n_devis" value="{{ old('n_devis', $intervention->n_devis) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>
        </div>

        {{-- COMMENTAIRES --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Commentaires</div>
            <textarea name="commentaires" rows="3" placeholder="Commentaire…" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical">{{ old('commentaires', $intervention->commentaires) }}</textarea>
        </div>

        {{-- LECTURE SEULE --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Informations Kizeo (lecture seule)</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° bon Kizeo</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</div>
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Statut</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888">{{ $intervention->statut ?? '—' }}</div>
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Origine</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888">{{ $intervention->source_kizeo ? 'Kizeo' : 'Manuel' }}</div>
                </div>
                <div style="grid-column:1/-1">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Demande annexe</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888;white-space:pre-wrap">{{ $intervention->demande_annexe ?? '—' }}</div>
                </div>
                <div style="grid-column:1/-1">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Pièces détachées</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888;white-space:pre-wrap">{{ $intervention->pieces_detachees ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <button type="submit" form="form-delete-intervention"
                    onclick="return confirm('Supprimer cette intervention ?')"
                    style="padding:8px 14px;font-size:12px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:8px;cursor:pointer">&#128465; Supprimer</button>
            <div style="display:flex;gap:8px">
                @if($intervention->contrat_id)
                <a href="{{ route('contrats.show', $intervention->contrat_id) }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
                @else
                <a href="{{ route('interventions.index') }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
                @endif
                <button type="submit" form="form-intervention" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">&#10003; Enregistrer</button>
            </div>
        </div>
    </form>
</div>

{{-- Formulaire de suppression séparé (hors du form d'édition pour éviter les forms imbriqués) --}}
<form id="form-delete-intervention" method="POST" action="{{ route('interventions.destroy', $intervention) }}">
    @csrf @method('DELETE')
</form>
@endsection
