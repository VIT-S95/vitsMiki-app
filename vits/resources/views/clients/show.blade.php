@extends('layouts.app')
@section('title', $client->nom_societe)
@section('content')
<a href="{{ route('clients.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour aux clients</a>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem;margin-bottom:1rem">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:0.75rem">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:48px;height:48px;border-radius:50%;background:#FFF3E6;color:#E8720C;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:500;border:1px solid #E8720C">{{ $client->initiales }}</div>
            <div>
                <div style="font-size:16px;font-weight:500;color:#1a1a1a">{{ $client->nom_societe }}</div>
                <div style="font-size:13px;color:#888;margin-top:2px">{{ $client->nom_signataire }}@if($client->email_signataire) · {{ $client->email_signataire }}@endif</div>
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
    @if($client->numero_contrat_vits)
    <div style="padding-top:0.75rem;border-top:1px solid #f0f0f0;display:flex;align-items:center;gap:8px">
        <span style="font-size:11px;color:#aaa;text-transform:uppercase">N° contrat VIT-S</span>
        <span style="font-family:monospace;font-size:13px;font-weight:500;background:#f5f5f5;padding:3px 10px;border-radius:4px">{{ $client->numero_contrat_vits }}</span>
        <span style="font-size:12px;color:#aaa">— N° Kizeo : {{ $client->numero_client_kizeo ?? '—' }}</span>
    </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1rem">
    <div style="background:#f5f5f5;border-radius:8px;padding:0.75rem 1rem;text-align:center">
        <div style="font-size:20px;font-weight:500;color:#7F77DD">{{ $client->contrats->count() }}</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">Renouvellements</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.75rem 1rem;text-align:center">
        <div style="font-size:20px;font-weight:500;color:#1a1a1a">0</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">Interventions totales</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.75rem 1rem;text-align:center">
        <div style="font-size:20px;font-weight:500;color:#E8720C">—</div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">Prochaine échéance</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">🏢 Informations</div>
        <div style="margin-bottom:0.75rem"><div style="font-size:11px;color:#aaa;text-transform:uppercase">Société</div><div style="font-size:13px">{{ $client->nom_societe }}</div></div>
        <div style="margin-bottom:0.75rem"><div style="font-size:11px;color:#aaa;text-transform:uppercase">Signataire</div><div style="font-size:13px">{{ $client->nom_signataire }}</div></div>
        <div style="margin-bottom:0.75rem"><div style="font-size:11px;color:#aaa;text-transform:uppercase">Email</div><div style="font-size:13px;color:#E8720C">{{ $client->email_signataire ?? '—' }}</div></div>
        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase">N° Kizeo</div><div style="font-size:13px;font-family:monospace">{{ $client->numero_client_kizeo ?? '—' }}</div></div>
    </div>
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">🔄 Historique des renouvellements</div>
        @forelse($client->contrats as $contrat)
            <div style="padding:10px 0;border-bottom:1px solid #f0f0f0">
                <div style="font-size:13px;font-weight:500">{{ $contrat->titre }}</div>
                <div style="font-size:12px;color:#888;margin-top:4px">{{ $contrat->duree_mois }} mois · {{ $contrat->heures_par_periode }}h / période</div>
            </div>
        @empty
            <div style="text-align:center;color:#aaa;font-size:13px;padding:1rem">Aucun contrat pour ce client</div>
        @endforelse
    </div>
</div>
@endsection
