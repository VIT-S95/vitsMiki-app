@extends('layouts.portail')
@section('title', 'Contrats')
@section('content')

<div style="margin-bottom:1.25rem">
    <div style="font-size:18px;font-weight:600;color:#1a1a1a">Contrats</div>
    <div style="font-size:13px;color:#888;margin-top:2px">Suivi de vos contrats de maintenance</div>
</div>

@forelse($client->contrats->sortByDesc('date_debut') as $contrat)
@php
    $actif = $contrat->statut === 'en-cours';
    $heuresAllouees = $contrat->heures_par_periode;

    // Période courante
    $currentPeriode = null;
    foreach ($contrat->getPeriodes() as $periode) {
        if (now()->between($periode['debut'], $periode['fin'])) {
            $currentPeriode = $periode;
            break;
        }
    }

    // Minutes consommées dans la période courante
    $minutesConsommees = 0;
    if ($currentPeriode) {
        $minutesConsommees = $contrat->interventions
            ->where('deductible', true)
            ->filter(fn($i) => $i->date_intervention >= $currentPeriode['debut']
                            && $i->date_intervention <= $currentPeriode['fin'])
            ->sum('duree_minutes');
    }

    $minutesAllouees = $heuresAllouees * 60;
    $pct = $minutesAllouees > 0 ? min(100, round($minutesConsommees / $minutesAllouees * 100)) : 0;
    $hC  = intdiv($minutesConsommees, 60);
    $mC  = $minutesConsommees % 60;
    $barColor = $pct >= 90 ? '#dc2626' : ($pct >= 75 ? '#E8720C' : '#166534');

    $totalInterventions = $contrat->interventions->count();
@endphp
<div style="background:#fff;border:1px solid {{ $actif ? '#E8720C' : '#e0e0e0' }};border-radius:12px;padding:1.25rem;margin-bottom:1rem">

    {{-- En-tête --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1rem">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                <span style="font-size:15px;font-weight:600;color:#1a1a1a">{{ $contrat->numero_contrat_vits ?? 'Contrat' }}</span>
                @if($actif)
                    <span style="background:#E8720C;color:#fff;font-size:10px;padding:1px 8px;border-radius:99px;font-weight:500">EN COURS</span>
                @elseif($contrat->statut === 'expire')
                    <span style="background:#f5f5f5;color:#aaa;font-size:10px;padding:1px 8px;border-radius:99px">Expiré</span>
                @else
                    <span style="background:#f5f5f5;color:#aaa;font-size:10px;padding:1px 8px;border-radius:99px">Inactif</span>
                @endif
            </div>
            <div style="font-size:12px;color:#888">
                {{ $contrat->date_debut?->format('d/m/Y') ?? '—' }} → {{ $contrat->date_fin?->format('d/m/Y') ?? 'indéterminé' }}
                &nbsp;·&nbsp;{{ $heuresAllouees }}h / période de {{ $contrat->duree_periode_mois }} mois
                &nbsp;·&nbsp;{{ $totalInterventions }} intervention{{ $totalInterventions > 1 ? 's' : '' }}
            </div>
        </div>
        @if($contrat->date_fin)
        <div style="text-align:right;font-size:12px;color:#888">
            <div style="font-weight:500;color:{{ $contrat->jours_restants < 60 && $actif ? '#E8720C' : '#1a1a1a' }}">{{ $contrat->echeance_label }}</div>
            <div style="color:#aaa">Échéance</div>
        </div>
        @endif
    </div>

    @if($actif && $currentPeriode)
    {{-- Consommation période courante --}}
    <div style="background:#f9f9f7;border-radius:8px;padding:1rem;margin-bottom:1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="font-size:12px;color:#888">Période du {{ $currentPeriode['debut']->format('d/m/Y') }} au {{ $currentPeriode['fin']->format('d/m/Y') }}</div>
            <div style="font-size:14px;font-weight:700;color:{{ $barColor }}">{{ $pct }}%</div>
        </div>
        <div style="background:#e0e0e0;border-radius:99px;height:8px;overflow:hidden;margin-bottom:6px">
            <div style="width:{{ $pct }}%;height:100%;background:{{ $barColor }};border-radius:99px"></div>
        </div>
        <div style="font-size:12px;color:#888">
            {{ $hC }}h{{ $mC > 0 ? ' '.$mC.'min' : '' }} consommées sur {{ $heuresAllouees }}h allouées
        </div>
    </div>
    @endif

    {{-- Périodes --}}
    @php $periodes = $contrat->getPeriodes()->filter(fn($p) => $p['debut']->lte(now())); @endphp
    @if($periodes->count() > 1)
    <details style="margin-top:0.25rem">
        <summary style="font-size:12px;color:#888;cursor:pointer;list-style:none;user-select:none">&#9654; Toutes les périodes ({{ $periodes->count() }})</summary>
        <div style="margin-top:0.75rem;display:flex;flex-direction:column;gap:6px">
            @foreach($periodes->sortByDesc('numero') as $p)
            @php
                $pMins = $contrat->interventions->where('deductible', true)
                    ->filter(fn($i) => $i->date_intervention >= $p['debut'] && $i->date_intervention <= $p['fin'])
                    ->sum('duree_minutes');
                $pH  = intdiv($pMins, 60); $pM = $pMins % 60;
                $pPct = $minutesAllouees > 0 ? min(100, round($pMins / $minutesAllouees * 100)) : 0;
                $pColor = $pPct >= 90 ? '#dc2626' : ($pPct >= 75 ? '#E8720C' : '#166534');
            @endphp
            <div style="display:flex;align-items:center;gap:12px;font-size:12px">
                <span style="color:#aaa;min-width:140px">P{{ $p['numero'] }} · {{ $p['debut']->format('d/m/Y') }} → {{ $p['fin']->format('d/m/Y') }}</span>
                <div style="flex:1;background:#f0f0f0;border-radius:99px;height:5px">
                    <div style="width:{{ $pPct }}%;height:100%;background:{{ $pColor }};border-radius:99px"></div>
                </div>
                <span style="color:#555;min-width:80px;text-align:right">{{ $pH }}h{{ $pM > 0 ? ' '.$pM.'min' : '' }} / {{ $heuresAllouees }}h</span>
            </div>
            @endforeach
        </div>
    </details>
    @endif

</div>
@empty
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:3rem;text-align:center;color:#aaa;font-size:13px">
    Aucun contrat enregistré.
</div>
@endforelse

@endsection
