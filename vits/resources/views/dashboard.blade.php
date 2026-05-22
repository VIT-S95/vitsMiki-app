@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Tableau de bord</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ now()->isoFormat('dddd D MMMM YYYY') }} — Vue d'ensemble des alertes actives</p>
    </div>
</div>

{{-- MÉTRIQUES --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:1rem">
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Clients actifs</div>
        <div style="font-size:22px;font-weight:500;color:#1a1a1a">{{ $totalClients }}</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Contrats en cours</div>
        <div style="font-size:22px;font-weight:500;color:#E8720C">{{ $totalContrats }}</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Interventions ce mois</div>
        <div style="font-size:22px;font-weight:500;color:#1a1a1a">{{ $interventionsMois }}</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Alertes actives</div>
        <div style="font-size:22px;font-weight:500;color:#dc2626">{{ $totalAlertes }}</div>
    </div>
</div>

{{-- KIZEO --}}
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:10px 16px;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:8px;background:#FFF3E6;display:flex;align-items:center;justify-content:center;font-size:18px">☁</div>
        <div>
            <div style="font-size:13px;font-weight:500">Synchronisation Kizeo</div>
            <div style="font-size:12px;color:#888;margin-top:1px">
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#166534;margin-right:4px;vertical-align:middle"></span>
                Dernière import : <strong>jamais</strong>
            </div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:16px">
        <div style="text-align:center"><div style="font-size:16px;font-weight:500">0</div><div style="font-size:11px;color:#aaa">sur site</div></div>
        <div style="width:1px;height:32px;background:#e0e0e0"></div>
        <div style="text-align:center"><div style="font-size:16px;font-weight:500">0</div><div style="font-size:11px;color:#aaa">à distance</div></div>
    </div>
</div>

{{-- GRILLE ALERTES --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">

    {{-- Contrats expirés --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#dc2626">⚠</span> Contrats terminés non renouvelés
            </div>
            <span style="background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $contratsExpires->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @forelse($contratsExpires as $contrat)
            <a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;justify-content:space-between;padding:7px 14px;text-decoration:none;color:#1a1a1a;border-bottom:1px solid #f5f5f5">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#fef2f2;border:1.5px solid #dc2626;display:inline-block"></span>
                    {{ $contrat->client->nom_societe }}
                </div>
                <span style="font-size:11px;color:#aaa">fin le {{ $contrat->date_fin->format('d/m/Y') }}</span>
            </a>
            @empty
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucun contrat expiré</div>
            @endforelse
        </div>
        <a href="{{ route('contrats.index', ['statut' => 'expire']) }}" style="display:block;font-size:11px;color:#E8720C;text-align:right;padding:6px 14px;border-top:1px solid #e0e0e0;text-decoration:none">Voir tous les contrats →</a>
    </div>

    {{-- Contrats échéance --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#E8720C">⏱</span> Contrats arrivant à échéance
            </div>
            <span style="background:#FFF3E6;color:#E8720C;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $contratsEcheance->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @forelse($contratsEcheance as $contrat)
            <a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;justify-content:space-between;padding:7px 14px;text-decoration:none;color:#1a1a1a;border-bottom:1px solid #f5f5f5">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#FFF3E6;border:1.5px solid #E8720C;display:inline-block"></span>
                    {{ $contrat->client->nom_societe }}
                </div>
                <span style="font-size:11px;color:#aaa">fin le {{ $contrat->date_fin->format('d/m/Y') }}</span>
            </a>
            @empty
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucun contrat en échéance proche</div>
            @endforelse
        </div>
        <a href="{{ route('contrats.index') }}" style="display:block;font-size:11px;color:#E8720C;text-align:right;padding:6px 14px;border-top:1px solid #e0e0e0;text-decoration:none">Voir tous les contrats →</a>
    </div>

    {{-- Interventions non traitées --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#E8720C">⚡</span> Interventions non traitées
            </div>
            <span style="background:#FFF3E6;color:#E8720C;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $interventionsNonTraitees->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @forelse($interventionsNonTraitees as $intervention)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 14px;border-bottom:1px solid #f5f5f5">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#FFF3E6;border:1.5px solid #E8720C;display:inline-block"></span>
                    {{ $intervention->contrat->client->nom_societe ?? '—' }} — {{ $intervention->type_tri }}
                </div>
                <span style="font-size:11px;color:#aaa">{{ $intervention->date_intervention->format('d/m') }}</span>
            </div>
            @empty
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucune intervention non traitée</div>
            @endforelse
        </div>
    </div>

    {{-- Alertes informatives --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#166534">ℹ</span> Alertes informatives
            </div>
            <span style="background:#f0fdf4;color:#166534;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $contratsBrouillon + $clientsSansInfos + $clientsSansContrat + $contratsDepassement->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @if($contratsBrouillon > 0)
            <a href="{{ route('contrats.index', ['statut' => 'non-actif']) }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $contratsBrouillon }} contrat(s) non actif(s) à valider
            </a>
            @endif
            @if($clientsSansInfos > 0)
            <a href="{{ route('clients.index') }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $clientsSansInfos }} client(s) avec infos incomplètes
            </a>
            @endif
            @if($clientsSansContrat > 0)
            <a href="{{ route('clients.index') }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $clientsSansContrat }} client(s) sans aucun contrat
            </a>
            @endif
            @foreach($contratsDepassement as $contrat)
            <a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $contrat->client->nom_societe }} — dépassement d'heures
            </a>
            @endforeach
            @if($contratsBrouillon + $clientsSansInfos + $clientsSansContrat + $contratsDepassement->count() === 0)
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucune alerte informative</div>
            @endif
        </div>
    </div>

</div>
@endsection
