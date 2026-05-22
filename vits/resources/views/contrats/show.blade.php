@extends('layouts.app')
@section('title', $contrat->client->nom_societe)
@section('content')
<a href="{{ route('contrats.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour aux contrats</a>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem;margin-bottom:1rem">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem">
        <div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <h1 style="font-size:16px;font-weight:500;color:#1a1a1a">{{ $contrat->client->nom_societe }}</h1>
                <span style="font-family:monospace;font-size:12px;background:#f5f5f5;padding:2px 8px;border-radius:4px;color:#888">{{ $contrat->numero_contrat_vits ?? '—' }}</span>
                @if($contrat->statut === 'en-cours')
                    <span style="background:#FFF3E6;color:#E8720C;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">En cours</span>
                @elseif($contrat->statut === 'expire')
                    <span style="background:#f5f5f5;color:#aaa;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">Expiré</span>
                @else
                    <span style="background:#f5f5f5;color:#888;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">Non actif</span>
                @endif
            </div>
            @if($contrat->numero_renouvellement > 0)
                <div style="font-size:12px;color:#888;margin-top:4px">Renouvellement {{ $contrat->numero_renouvellement }}</div>
            @else
                <div style="font-size:12px;color:#888;margin-top:4px">Contrat initial</div>
            @endif
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('contrats.edit', $contrat) }}" style="padding:6px 12px;font-size:12px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">✏ Modifier</a>
            <a href="{{ route('interventions.create', ['contrat_id' => $contrat->id]) }}" style="padding:6px 12px;font-size:12px;background:#E8720C;color:#fff;border-radius:8px;text-decoration:none">+ Intervention</a>
        </div>
    </div>
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid #f0f0f0">
        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase">Début</div><div style="font-size:13px;font-weight:500">{{ $contrat->date_debut?->format('d/m/Y') ?? '—' }}</div></div>
        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase">Fin</div><div style="font-size:13px;font-weight:500">{{ $contrat->date_fin?->format('d/m/Y') ?? '—' }}</div></div>
        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase">Durée</div><div style="font-size:13px;font-weight:500">{{ $contrat->duree_mois == 12 ? '1 an' : ($contrat->duree_mois == 24 ? '2 ans' : '3 ans') }}</div></div>
        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase">Période</div><div style="font-size:13px;font-weight:500">{{ $contrat->duree_periode_mois }} mois</div></div>
        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase">H / période</div><div style="font-size:13px;font-weight:500">{{ $contrat->heures_par_periode }}h</div></div>
        <div><div style="font-size:11px;color:#aaa;text-transform:uppercase">Échéance</div>
            <div style="font-size:13px;font-weight:500;color:{{ ($contrat->jours_restants ?? 0) < 0 ? '#aaa' : (($contrat->jours_restants ?? 999) <= 90 ? '#E8720C' : '#166534') }}">{{ $contrat->echeance_label }}</div>
        </div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1rem">
    <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:0.75rem">Progression</div>
    <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:12px;color:#888;width:220px">Avancement du contrat</span>
        <div style="flex:1;height:8px;background:#f0f0f0;border-radius:99px;overflow:hidden">
            <div style="width:{{ $contrat->avancement }}%;height:100%;background:{{ $contrat->avancement >= 80 ? '#E8720C' : '#166534' }};border-radius:99px"></div>
        </div>
        <span style="font-size:12px;font-weight:500;min-width:36px;text-align:right">{{ $contrat->avancement }}%</span>
    </div>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1rem 1.25rem">
    <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:0.75rem">Périodes et interventions</div>

    @foreach($periodes as $periode)
    @php
        $interventionsPeriode = $contrat->interventions->filter(function($i) use ($periode) {
            return $i->date_intervention >= $periode['debut'] && $i->date_intervention <= $periode['fin'];
        });
        $minutesConsommees = $interventionsPeriode->sum('duree_minutes');
        $heuresConsommees = floor($minutesConsommees / 60);
        $minRestants = $minutesConsommees % 60;
        $heuresAllouees = $contrat->heures_par_periode;
        $pct = $heuresAllouees > 0 ? min(100, round(($minutesConsommees / ($heuresAllouees * 60)) * 100)) : 0;
        $couleur = $pct >= 80 ? '#E8720C' : '#166534';
        $enCours = now()->between($periode['debut'], $periode['fin']);
    @endphp
    <div style="border:1px solid #e0e0e0;border-radius:8px;margin-bottom:8px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f5f5f5;cursor:pointer"
             onclick="var el=this.nextElementSibling;el.style.display=el.style.display==='none'?'block':'none'">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                ▶ Année {{ $periode['numero'] }} — {{ $periode['debut']->year }}/{{ $periode['fin']->year }}
                @if($enCours)<span style="font-size:11px;color:#888;font-weight:400">(période en cours)</span>@endif
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-size:12px;color:#888">{{ $heuresConsommees }}h @if($minRestants > 0){{ $minRestants }}min @endif / {{ $heuresAllouees }}h</span>
                <div style="width:80px;height:5px;background:#e0e0e0;border-radius:99px;overflow:hidden">
                    <div style="width:{{ $pct }}%;height:100%;background:{{ $couleur }};border-radius:99px"></div>
                </div>
                <span style="font-size:11px;font-weight:500;color:{{ $couleur }}">{{ $pct }}%</span>
            </div>
        </div>
        <div style="display:{{ $enCours ? 'block' : 'none' }}">
            @if($interventionsPeriode->count() > 0)
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="border-bottom:1px solid #f0f0f0">
                        <th style="padding:6px 12px;text-align:left;font-size:10px;color:#aaa;text-transform:uppercase">Date</th>
                        <th style="padding:6px 12px;text-align:left;font-size:10px;color:#aaa;text-transform:uppercase">N° bon Kizeo</th>
                        <th style="padding:6px 12px;text-align:left;font-size:10px;color:#aaa;text-transform:uppercase">Type</th>
                        <th style="padding:6px 12px;text-align:right;font-size:10px;color:#aaa;text-transform:uppercase">Durée</th>
                        <th style="padding:6px 12px;border-bottom:1px solid #f0f0f0"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($interventionsPeriode as $intervention)
                    <tr style="border-top:1px solid #f5f5f5">
                        <td style="padding:6px 12px;color:#888">{{ $intervention->date_intervention->format('d/m/Y') }}</td>
                        <td style="padding:6px 12px;font-family:monospace;font-size:11px;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</td>
                        <td style="padding:6px 12px">
                            @if($intervention->type === 'site')
                                <span style="background:#E6F1FB;color:#0C447C;padding:2px 6px;border-radius:4px;font-size:10px">sur site</span>
                            @elseif($intervention->type === 'distance')
                                <span style="background:#E1F5EE;color:#085041;padding:2px 6px;border-radius:4px;font-size:10px">à distance</span>
                            @else
                                <span style="background:#FFF3E6;color:#854F0B;padding:2px 6px;border-radius:4px;font-size:10px">flash {{ $intervention->flash_numero }}/3</span>
                            @endif
                        </td>
                        <td style="padding:6px 12px;text-align:right;font-weight:500">
                            @if($intervention->type === 'flash' && $intervention->flash_numero < 3)
                                —
                            @else
                                @php $h = floor($intervention->duree_minutes/60); $m = $intervention->duree_minutes%60; @endphp
                                @if($h > 0){{ $h }}h @endif
                                @if($m > 0){{ $m }}min @endif
                            @endif
                        </td>
                        <td style="padding:6px 12px;text-align:right">
                            <a href="{{ route('interventions.edit', $intervention) }}" style="font-size:11px;color:#888;text-decoration:none;padding:3px 7px;border:1px solid #ddd;border-radius:5px">✏</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="padding:12px;text-align:center;color:#aaa;font-size:12px;font-style:italic">Aucune intervention pour cette période</div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
