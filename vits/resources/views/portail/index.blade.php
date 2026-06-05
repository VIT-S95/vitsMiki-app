@extends('layouts.portail')
@section('title', 'Tableau de bord')
@section('content')

<div style="margin-bottom:1.5rem">
    <div style="font-size:22px;font-weight:600;color:#1a1a1a">Bienvenue, {{ $client->nom_societe }}</div>
    <div style="font-size:13px;color:#888;margin-top:4px">Votre espace client VIT-S</div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">

    {{-- Contrat en cours --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:11px;color:#aaa;text-transform:uppercase;margin-bottom:0.75rem">Contrat en cours</div>
        @if($contratActif)
            @php
                $pct = $minutesAllouees > 0 ? min(100, round($minutesConsommees / $minutesAllouees * 100)) : 0;
                $hC  = intdiv($minutesConsommees, 60);
                $mC  = $minutesConsommees % 60;
                $hA  = $contratActif->heures_par_periode;
                $barColor = $pct >= 90 ? '#dc2626' : ($pct >= 75 ? '#E8720C' : '#166534');
            @endphp
            <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:4px">{{ $contratActif->numero_contrat_vits ?? 'Contrat actif' }}</div>
            <div style="font-size:24px;font-weight:700;color:{{ $barColor }};margin-bottom:4px">{{ $pct }}%</div>
            <div style="font-size:12px;color:#888;margin-bottom:10px">
                {{ $hC }}h{{ $mC > 0 ? ' '.$mC.'min' : '' }} / {{ $hA }}h
                @if($currentPeriode)
                    <span style="color:#aaa"> — période {{ $currentPeriode['debut']->format('d/m') }} → {{ $currentPeriode['fin']->format('d/m/Y') }}</span>
                @endif
            </div>
            <div style="background:#f0f0f0;border-radius:99px;height:6px;overflow:hidden">
                <div style="width:{{ $pct }}%;height:100%;background:{{ $barColor }};border-radius:99px;transition:width .4s"></div>
            </div>
        @else
            <div style="color:#aaa;font-size:13px">Aucun contrat actif</div>
        @endif
    </div>

    {{-- Dernière intervention --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:11px;color:#aaa;text-transform:uppercase;margin-bottom:0.75rem">Dernière intervention</div>
        @if($derniereIntervention)
            @php
                $di   = $derniereIntervention;
                $dH   = intdiv(abs($di->duree_minutes), 60);
                $dM   = abs($di->duree_minutes) % 60;
                $dStr = ($dH > 0 ? $dH.'h ' : '').($dM > 0 ? $dM.'min' : '');
            @endphp
            <div style="font-size:20px;font-weight:700;color:#1a1a1a;margin-bottom:4px">{{ $di->date_intervention->format('d/m/Y') }}</div>
            <div style="font-size:13px;color:#555;margin-bottom:4px">{{ $di->technicien ?? '—' }}</div>
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                @if($di->type === 'site')      <span style="background:#E6F1FB;color:#0C447C;padding:2px 7px;border-radius:4px;font-size:11px">Sur site</span>
                @elseif($di->type === 'distance') <span style="background:#E1F5EE;color:#085041;padding:2px 7px;border-radius:4px;font-size:11px">À distance</span>
                @elseif($di->type === 'administrateur') <span style="background:#F3F0FF;color:#4C1D95;padding:2px 7px;border-radius:4px;font-size:11px">Admin</span>
                @else <span style="background:#FFF3E6;color:#854F0B;padding:2px 7px;border-radius:4px;font-size:11px">Flash</span>
                @endif
                @if($dStr)<span style="font-size:12px;color:#888">{{ $dStr }}</span>@endif
            </div>
        @else
            <div style="color:#aaa;font-size:13px">Aucune intervention</div>
        @endif
    </div>

    {{-- Prochain renouvellement --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:11px;color:#aaa;text-transform:uppercase;margin-bottom:0.75rem">Prochain renouvellement</div>
        @if($contratActif && $contratActif->date_fin)
            @php
                $jours = $contratActif->jours_restants;
                $urgence = $jours !== null && $jours < 60;
            @endphp
            <div style="font-size:20px;font-weight:700;color:{{ $urgence ? '#E8720C' : '#1a1a1a' }};margin-bottom:4px">
                {{ $contratActif->date_fin->format('d/m/Y') }}
            </div>
            <div style="font-size:13px;color:#888;margin-bottom:4px">{{ $contratActif->echeance_label }}</div>
            @if($urgence)
                <span style="background:#FFF3E6;color:#E8720C;padding:2px 8px;border-radius:4px;font-size:11px">Renouvellement proche</span>
            @endif
        @elseif($contratActif)
            <div style="font-size:13px;color:#aaa">Durée indéterminée</div>
        @else
            <div style="color:#aaa;font-size:13px">Aucun contrat actif</div>
        @endif
    </div>

</div>

{{-- Liens rapides --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
    <a href="{{ route('portail.interventions') }}" style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:1rem 1.25rem;text-decoration:none;display:flex;align-items:center;gap:10px;color:#1a1a1a">
        <span style="font-size:20px">📋</span>
        <div><div style="font-size:13px;font-weight:500">Interventions</div><div style="font-size:11px;color:#aaa;margin-top:2px">Historique complet</div></div>
    </a>
    <a href="{{ route('portail.contrats') }}" style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:1rem 1.25rem;text-decoration:none;display:flex;align-items:center;gap:10px;color:#1a1a1a">
        <span style="font-size:20px">📄</span>
        <div><div style="font-size:13px;font-weight:500">Contrats</div><div style="font-size:11px;color:#aaa;margin-top:2px">Détail et suivi</div></div>
    </a>
    <a href="{{ route('portail.documents') }}" style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:1rem 1.25rem;text-decoration:none;display:flex;align-items:center;gap:10px;color:#1a1a1a">
        <span style="font-size:20px">⬇</span>
        <div><div style="font-size:13px;font-weight:500">Documents</div><div style="font-size:11px;color:#aaa;margin-top:2px">Rapports PDF</div></div>
    </a>
</div>

@endsection
