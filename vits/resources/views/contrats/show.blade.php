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
            <a href="{{ route('contrats.pdf.choix', $contrat) }}" style="padding:6px 12px;font-size:12px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">📄 PDF</a>
            @if($contrat->statut === 'en-cours')
            <form method="POST" action="{{ route('contrats.reimport-kizeo', $contrat) }}" style="display:inline">
                @csrf
                <button type="submit" style="padding:6px 12px;font-size:12px;border:1px solid #E8720C;border-radius:8px;color:#E8720C;background:#fff;cursor:pointer">🔄 Récupérer interventions manquantes</button>
            </form>
            @endif
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
        $minutesConsommees = $interventionsPeriode->where('deductible', true)->sum('duree_minutes');
        $heuresConsommees = floor($minutesConsommees / 60);
        $minRestants = $minutesConsommees % 60;
        $heuresAllouees = $contrat->heures_par_periode;
        $pct = $heuresAllouees > 0 ? min(100, round(($minutesConsommees / ($heuresAllouees * 60)) * 100)) : 0;
        $couleur = $pct >= 80 ? '#E8720C' : '#166534';
        $enCours = now()->between($periode['debut'], $periode['fin']);
        $moisList = collect();
        $cur = $periode['debut']->copy()->startOfMonth();
        while ($cur->lte($periode['fin'])) {
            $debutMois = $cur->copy()->max($periode['debut']);
            $finMois   = $cur->copy()->endOfMonth()->min($periode['fin']);
            $intsMois  = $interventionsPeriode->filter(function($i) use ($debutMois, $finMois) {
                return $i->date_intervention >= $debutMois && $i->date_intervention <= $finMois;
            })->sortBy('date_intervention');
            $minsMois = $intsMois->where('deductible', true)->sum('duree_minutes');
            $moisList->push(['label' => ucfirst($cur->isoFormat('MMMM YYYY')), 'ints' => $intsMois, 'h' => floor($minsMois/60), 'm' => $minsMois%60]);
            $cur->addMonth()->startOfMonth();
        }
    @endphp
    <div style="border:1px solid #e0e0e0;border-radius:8px;margin-bottom:8px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f5f5f5;cursor:pointer"
             onclick="var el=this.nextElementSibling;el.style.display=el.style.display==='none'?'block':'none'">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                ▶ Année {{ $periode['numero'] }} — {{ $periode['debut']->format('d/m/Y') }} / {{ $periode['fin']->format('d/m/Y') }}
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
                @foreach($moisList as $mois)
                @if($mois['ints']->count() > 0)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 12px;background:#fafafa;border-top:1px solid #e0e0e0;border-bottom:1px solid #e8e8e8">
                    <span style="font-size:11px;font-weight:500;color:#555;text-transform:uppercase;letter-spacing:0.5px">{{ $mois['label'] }}</span>
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <thead>
                        <tr style="border-bottom:1px solid #f0f0f0;background:#fefefe">
                            <th style="padding:5px 12px;text-align:left;font-size:10px;color:#aaa;text-transform:uppercase;width:90px">Date</th>
                            <th style="padding:5px 12px;text-align:left;font-size:10px;color:#aaa;text-transform:uppercase">N° Intervention</th>
                            <th style="padding:5px 12px;text-align:left;font-size:10px;color:#aaa;text-transform:uppercase">Type</th>
                            <th style="padding:5px 12px;text-align:right;font-size:10px;color:#aaa;text-transform:uppercase;width:80px">Durée</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mois['ints'] as $intervention)
                        @php $nonDed = !$intervention->deductible; @endphp
                        <tr style="border-top:1px solid #f5f5f5;{{ $nonDed ? 'opacity:0.45' : '' }}">
                            <td style="padding:6px 12px;color:#888;width:90px">{{ $intervention->date_intervention->format('d/m/Y') }}</td>
                            <td style="padding:6px 12px;font-family:monospace;font-size:11px;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</td>
                            <td style="padding:6px 12px">
                                @if($intervention->type === 'site') <span style="background:#E6F1FB;color:#0C447C;padding:2px 6px;border-radius:4px;font-size:10px">sur site</span>
                                @elseif($intervention->type === 'distance') <span style="background:#E1F5EE;color:#085041;padding:2px 6px;border-radius:4px;font-size:10px">à distance</span>
                                @elseif($intervention->type === 'administrateur') <span style="background:#F3F0FF;color:#4C1D95;padding:2px 6px;border-radius:4px;font-size:10px">admin</span>
                                @else <span style="background:#FFF3E6;color:#854F0B;padding:2px 6px;border-radius:4px;font-size:10px">flash {{ $intervention->flash_numero }}/3</span>
                                @endif
                                @if($nonDed)<span style="font-size:10px;color:#aaa;margin-left:4px">hors contrat</span>@endif
                                @if($intervention->motif)<span style="font-size:10px;color:#aaa;margin-left:4px">— {{ $intervention->motif }}</span>@endif
                            </td>
                            <td style="padding:6px 12px;text-align:right;font-weight:500;width:80px">
                                @if($intervention->type === 'flash' && $intervention->flash_numero < 3) —
                                @elseif($intervention->duree_minutes < 0)
                                    @php $abs=abs($intervention->duree_minutes); $h=floor($abs/60); $m=$abs%60; @endphp
                                    <span style="color:#166534">+{{ $h>0?$h.'h ':''}}{{ $m>0?$m.'min':''  }}</span>
                                @else
                                    @php $h=floor($intervention->duree_minutes/60); $m=$intervention->duree_minutes%60; @endphp
                                    {{ $h>0?$h.'h ':'' }}{{ $m>0?$m.'min':'' }}
                                @endif
                            </td>
                            <td style="padding:6px 12px;text-align:right;width:40px">
                                <a href="{{ route('interventions.edit', $intervention) }}" style="font-size:11px;color:#888;text-decoration:none;padding:3px 7px;border:1px solid #ddd;border-radius:5px">✏</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid #e0e0e0;background:#fafafa">
                            <td colspan="3" style="padding:5px 12px;font-size:11px;color:#555;font-weight:500;text-transform:uppercase;letter-spacing:0.5px">Total {{ $mois['label'] }}</td>
                            <td style="padding:5px 12px;text-align:right;font-size:11px;font-weight:600;color:#1a1a1a">{{ $mois['h'] > 0 ? $mois['h'].'h' : '' }}{{ $mois['m'] > 0 ? $mois['m'].'min' : '' }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                @endif
                @endforeach
            @else
            <div style="padding:1rem 12px;color:#aaa;font-size:12px;text-align:center">Aucune intervention sur cette période</div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@if(session('success'))
<div id="success-modal" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:9999">
    <div style="background:#fff;border-radius:12px;padding:2rem 2.5rem;max-width:480px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center">
        <div style="font-size:24px;margin-bottom:0.75rem">✅</div>
        <div style="font-size:14px;color:#1a1a1a;line-height:1.6;margin-bottom:1.5rem">{{ session('success') }}</div>
        <button onclick="document.getElementById('success-modal').remove()" style="padding:8px 24px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer">OK</button>
    </div>
</div>
@endif
@endsection