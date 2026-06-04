@extends('layouts.app')
@section('title', $client->nom_societe)
@section('content')
<a href="{{ route('clients.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour aux clients</a>

@php
    $contratActif = $client->contrats->where('statut','en-cours')->sortByDesc('date_debut')->first();
    $totalInterventions = $client->contrats->sum(fn($c) => $c->interventions->count());
    $prochaineEcheance = $contratActif?->echeance_label ?? '—';
@endphp

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem;margin-bottom:1rem">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:0.75rem">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:48px;height:48px;border-radius:50%;background:#FFF3E6;color:#E8720C;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:500;border:1px solid #E8720C">{{ $client->initiales }}</div>
            <div>
                <div style="font-size:16px;font-weight:500;color:#1a1a1a">{{ $client->nom_societe }}</div>
                <div style="font-size:13px;color:#888;margin-top:2px">{{ $client->nom_signataire }}@if($client->email_signataire) · {{ $client->email_signataire }}@endif</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px;font-family:monospace">N° Kizeo : {{ $client->numero_client_kizeo ?? '—' }}</div>
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('clients.edit', $client) }}" style="padding:6px 12px;font-size:12px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">✏ Modifier</a>
            <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Supprimer ce client ?')">
                @csrf @method('DELETE')
                <button type="submit" style="padding:6px 12px;font-size:12px;border:1px solid #ddd;border-radius:8px;color:#dc2626;background:#fff;cursor:pointer">🗑 Supprimer</button>
            </form>
        </div>
    </div>
</div>

{{-- STATS --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1rem">
    <div style="background:#f5f5f5;border-radius:8px;padding:0.75rem 1rem;text-align:center">
        <div style="font-size:20px;font-weight:500;color:#7F77DD">{{ $client->contrats->count() }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">Contrats au total</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.75rem 1rem;text-align:center">
        <div style="font-size:20px;font-weight:500;color:#1a1a1a">{{ $totalInterventions }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">Interventions totales</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.75rem 1rem;text-align:center">
        <div style="font-size:20px;font-weight:500;color:#E8720C">{{ $prochaineEcheance }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">Échéance contrat actif</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    {{-- INFOS --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">🏢 Informations</div>
        <div style="margin-bottom:0.75rem"><div style="font-size:11px;color:#aaa;text-transform:uppercase">Société</div><div style="font-size:13px">{{ $client->nom_societe }}</div></div>
        <div style="margin-bottom:0.75rem"><div style="font-size:11px;color:#aaa;text-transform:uppercase">Signataire</div><div style="font-size:13px">{{ $client->nom_signataire }}</div></div>
        <div style="margin-bottom:0.75rem"><div style="font-size:11px;color:#aaa;text-transform:uppercase">Email</div><div style="font-size:13px;color:#E8720C">{{ $client->email_signataire ?? '—' }}</div></div>
        <div style="margin-bottom:0.75rem"><div style="font-size:11px;color:#aaa;text-transform:uppercase">N° Kizeo</div><div style="font-size:13px;font-family:monospace">{{ $client->numero_client_kizeo ?? '—' }}</div></div>

        {{-- Paramètres mail --}}
        <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid #f0f0f0">
            <div style="font-size:11px;color:#aaa;text-transform:uppercase;margin-bottom:8px">Paramètres mail</div>
            <div style="display:flex;flex-direction:column;gap:6px">
                <div style="display:flex;align-items:center;gap:8px">
                    @if($client->mail_alerte_fin_contrat)
                        <span style="color:#166534;font-size:13px">&#10003;</span>
                        <span style="font-size:12px;color:#1a1a1a">Alertes fin de contrat activées</span>
                    @else
                        <span style="color:#aaa;font-size:13px">&#8212;</span>
                        <span style="font-size:12px;color:#aaa">Pas d'alerte fin de contrat</span>
                    @endif
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    @if($client->mail_rapport_periodique)
                        @php $freq = ['mensuel'=>'Mensuel','hebdo'=>'Hebdomadaire','trimestriel'=>'Trimestriel'][$client->mail_frequence] ?? 'Mensuel'; @endphp
                        <span style="color:#166534;font-size:13px">&#10003;</span>
                        <span style="font-size:12px;color:#1a1a1a">Rapport périodique — {{ $freq }}</span>
                    @else
                        <span style="color:#aaa;font-size:13px">&#8212;</span>
                        <span style="font-size:12px;color:#aaa">Pas de rapport périodique</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- HISTORIQUE CONTRATS --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">🔄 Historique des contrats</div>
        @forelse($client->contrats->sortByDesc('date_debut') as $contrat)
        @php $actif = $contrat->statut === 'en-cours'; @endphp
        <div style="padding:10px 12px;margin-bottom:6px;border-radius:8px;border:1px solid {{ $actif ? '#E8720C' : '#e0e0e0' }};background:{{ $actif ? '#FFF9F5' : '#fff' }}">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                <div>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="font-size:12px;font-weight:500;color:{{ $actif ? '#E8720C' : '#1a1a1a' }}">{{ $contrat->numero_contrat_vits ?? 'Contrat' }}</span>
                        @if($actif)
                            <span style="background:#E8720C;color:#fff;font-size:10px;padding:1px 6px;border-radius:99px;font-weight:500">EN COURS</span>
                        @elseif($contrat->statut === 'expire')
                            <span style="background:#f5f5f5;color:#aaa;font-size:10px;padding:1px 6px;border-radius:99px">Expiré</span>
                        @else
                            <span style="background:#f5f5f5;color:#aaa;font-size:10px;padding:1px 6px;border-radius:99px">Non actif</span>
                        @endif
                    </div>
                    <div style="font-size:11px;color:#888;margin-top:3px">
                        {{ $contrat->date_debut?->format('d/m/Y') ?? '—' }} → {{ $contrat->date_fin?->format('d/m/Y') ?? '—' }}
                        · {{ $contrat->duree_mois == 12 ? '1 an' : ($contrat->duree_mois == 24 ? '2 ans' : '3 ans') }}
                        · {{ $contrat->heures_par_periode }}h / période
                    </div>
                </div>
                @if($actif)
                <a href="{{ route('contrats.show', $contrat) }}" style="padding:5px 10px;font-size:11px;background:#E8720C;color:#fff;border-radius:6px;text-decoration:none;white-space:nowrap">Voir →</a>
                @else
                <a href="{{ route('contrats.show', $contrat) }}" style="padding:5px 10px;font-size:11px;border:1px solid #ddd;color:#888;border-radius:6px;text-decoration:none;white-space:nowrap">Voir</a>
                @endif
            </div>
        </div>
        @empty
        <div style="text-align:center;color:#aaa;font-size:13px;padding:1rem">Aucun contrat pour ce client</div>
        @endforelse

        @if($contratActif)
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f0f0f0">
            <a href="{{ route('contrats.create', ['client_id' => $client->id]) }}" style="font-size:12px;color:#888;text-decoration:none">+ Nouveau contrat</a>
        </div>
        @else
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f0f0f0">
            <a href="{{ route('contrats.create', ['client_id' => $client->id]) }}" style="padding:6px 12px;font-size:12px;background:#E8720C;color:#fff;border-radius:8px;text-decoration:none">+ Créer un contrat</a>
        </div>
        @endif
    </div>
</div>
@endsection
