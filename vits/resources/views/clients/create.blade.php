@extends('layouts.app')
@section('title', 'Nouveau client')
@section('content')
<a href="{{ route('clients.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:1.5rem">Nouveau client</h1>

<div style="background:#f5f5f5;border:1px solid #e0e0e0;border-radius:8px;padding:10px 14px;font-size:12px;color:#666;margin-bottom:1.5rem">
    ℹ️ Les clients sont créés automatiquement via Kizeo. Ce formulaire sert uniquement pour une création manuelle.
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Nom de la société <span style="color:#E8720C">*</span></label>
                <input type="text" name="nom_societe" value="{{ old('nom_societe') }}" placeholder="Clinique du Parc" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                @error('nom_societe')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° client Kizeo <span style="color:#E8720C">*</span></label>
                <input type="text" name="numero_client_kizeo" value="{{ old('numero_client_kizeo') }}" placeholder="KZ-00142" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                <div style="font-size:11px;color:#aaa;margin-top:3px">Clé de liaison avec les interventions Kizeo</div>
                @error('numero_client_kizeo')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Nom du signataire <span style="color:#E8720C">*</span></label>
                <input type="text" name="nom_signataire" value="{{ old('nom_signataire') }}" placeholder="Dr. Fontaine" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                @error('nom_signataire')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Email du signataire</label>
                <input type="email" name="email_signataire" value="{{ old('email_signataire') }}" placeholder="contact@example.fr" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            </div>
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° contrat VIT-S</label>
                <input type="text" name="numero_contrat_vits" value="{{ old('numero_contrat_vits') }}" placeholder="VIT-2026-051" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                <div style="font-size:11px;color:#aaa;margin-top:3px">Attribué lors de la première signature</div>
            </div>
            <div>
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Statut</label>
                <select name="statut" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <option value="actif" {{ old('statut') === 'actif' ? 'selected' : '' }}>Sous contrat</option>
                    <option value="sans_contrat" {{ old('statut', 'sans_contrat') === 'sans_contrat' ? 'selected' : '' }}>Sans contrat</option>
                    <option value="inactif" {{ old('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
        </div>
        {{-- Paramètres mail --}}
        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">Paramètres mail</div>
            <div style="display:flex;flex-direction:column;gap:12px">
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
                    <input type="checkbox" name="mail_alerte_fin_contrat" value="1"
                           {{ old('mail_alerte_fin_contrat') ? 'checked' : '' }}
                           style="width:16px;height:16px;margin-top:2px;accent-color:#E8720C;cursor:pointer;flex-shrink:0">
                    <div>
                        <div style="font-size:13px;color:#1a1a1a">Recevoir les alertes de fin de contrat</div>
                        <div style="font-size:12px;color:#aaa;margin-top:2px">Un email est envoyé lorsque l'échéance du contrat approche</div>
                    </div>
                </label>
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
                    <input type="checkbox" name="mail_rapport_periodique" value="1" id="chk-rapport"
                           onchange="document.getElementById('freq-row').style.display=this.checked?'flex':'none'"
                           {{ old('mail_rapport_periodique') ? 'checked' : '' }}
                           style="width:16px;height:16px;margin-top:2px;accent-color:#E8720C;cursor:pointer;flex-shrink:0">
                    <div>
                        <div style="font-size:13px;color:#1a1a1a">Recevoir les rapports périodiques</div>
                        <div style="font-size:12px;color:#aaa;margin-top:2px">Un rapport d'interventions est envoyé selon la fréquence choisie</div>
                    </div>
                </label>
                <div id="freq-row" style="display:{{ old('mail_rapport_periodique') ? 'flex' : 'none' }};align-items:center;gap:10px;padding-left:26px">
                    <label style="font-size:12px;color:#666;white-space:nowrap">Fréquence :</label>
                    <select name="mail_frequence" style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="mensuel"     {{ old('mail_frequence') === 'mensuel'     ? 'selected' : '' }}>Mensuel</option>
                        <option value="hebdo"       {{ old('mail_frequence') === 'hebdo'       ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="trimestriel" {{ old('mail_frequence') === 'trimestriel' ? 'selected' : '' }}>Trimestriel</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <a href="{{ route('clients.index') }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Créer le client</button>
        </div>
    </form>
</div>
@endsection
