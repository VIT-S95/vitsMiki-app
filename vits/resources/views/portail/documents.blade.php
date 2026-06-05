@extends('layouts.portail')
@section('title', 'Documents')
@section('content')

<div style="margin-bottom:1.25rem">
    <div style="font-size:18px;font-weight:600;color:#1a1a1a">Documents</div>
    <div style="font-size:13px;color:#888;margin-top:2px">Rapports PDF de vos contrats</div>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    @forelse($client->contrats->sortByDesc('date_debut') as $contrat)
    @php
        $actif = $contrat->statut === 'en-cours';
        $nbInterventions = $contrat->interventions->count();
    @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f5f5f5">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                <span style="font-size:13px;font-weight:500;color:#1a1a1a">{{ $contrat->numero_contrat_vits ?? 'Contrat' }}</span>
                @if($actif)
                    <span style="background:#E8720C;color:#fff;font-size:10px;padding:1px 7px;border-radius:99px;font-weight:500">EN COURS</span>
                @else
                    <span style="background:#f5f5f5;color:#aaa;font-size:10px;padding:1px 7px;border-radius:99px">{{ $contrat->statut === 'expire' ? 'Expiré' : 'Inactif' }}</span>
                @endif
            </div>
            <div style="font-size:12px;color:#aaa">
                {{ $contrat->date_debut?->format('d/m/Y') ?? '—' }} → {{ $contrat->date_fin?->format('d/m/Y') ?? 'indéterminé' }}
                &nbsp;·&nbsp;{{ $nbInterventions }} intervention{{ $nbInterventions > 1 ? 's' : '' }}
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="{{ route('contrats.pdf.choix', $contrat) }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;font-weight:500;background:#E8720C;color:#fff;border-radius:8px;text-decoration:none;white-space:nowrap">
                ⬇ Télécharger le rapport PDF
            </a>
        </div>
    </div>
    @empty
    <div style="padding:3rem;text-align:center;color:#aaa;font-size:13px">Aucun contrat enregistré.</div>
    @endforelse
</div>

<div style="margin-top:1rem;padding:12px 16px;background:#f9f9f7;border-radius:8px;font-size:12px;color:#888">
    Les rapports PDF incluent le détail de toutes les interventions par période.
    Sélectionnez la période souhaitée après avoir cliqué sur "Télécharger".
</div>

@endsection
