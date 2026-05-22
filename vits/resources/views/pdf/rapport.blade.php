<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 12px; color: #2C2C2A; }
.header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 2px solid #E8720C; margin-bottom: 20px; }
.logo-badge { background: #E8720C; color: #fff; font-size: 16px; font-weight: bold; padding: 5px 14px; border-radius: 4px; display: inline-block; }
.logo-sub { font-size: 11px; color: #888; margin-top: 4px; }
.header-right { text-align: right; font-size: 11px; color: #888; }
.client-band { background: #F1EFE8; border-radius: 4px; padding: 12px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; }
.client-name { font-size: 15px; font-weight: bold; color: #1A1A1A; }
.client-meta { font-size: 11px; color: #5F5E5A; margin-top: 3px; }
.contrat-ref { text-align: right; font-size: 11px; color: #888; }
.contrat-ref strong { display: block; font-size: 12px; color: #444; font-family: monospace; }
.synthese { display: flex; gap: 10px; margin-bottom: 20px; }
.syn { flex: 1; border: 1px solid #D3D1C7; border-radius: 4px; padding: 10px; text-align: center; }
.syn-val { font-size: 18px; font-weight: bold; color: #1A1A1A; }
.syn-val-orange { color: #E8720C; }
.syn-val-green { color: #0F6E56; }
.syn-label { font-size: 10px; color: #888; margin-top: 2px; }
.section-title { font-size: 10px; font-weight: bold; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; margin-top: 20px; }
.periode-title { display: flex; justify-content: space-between; background: #F1EFE8; padding: 6px 10px; border-radius: 4px 4px 0 0; font-size: 12px; font-weight: bold; }
.mois-title { display: flex; justify-content: space-between; padding: 5px 10px; background: #fafafa; border-top: 1px solid #e0e0e0; font-size: 11px; font-weight: bold; color: #444; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th { padding: 5px 8px; text-align: left; font-size: 10px; color: #888; text-transform: uppercase; border-bottom: 1px solid #D3D1C7; background: #fff; }
td { padding: 5px 8px; border-bottom: 1px solid #F1EFE8; color: #444; }
.tag-site { background: #E6F1FB; color: #0C447C; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.tag-dist { background: #E1F5EE; color: #085041; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.tag-flash { background: #FFF3E6; color: #854F0B; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.total-row td { background: #F1EFE8; font-weight: bold; }
.bilan { border: 2px solid #E8720C; border-radius: 4px; padding: 12px 16px; margin: 15px 0; background: #FFF3E6; }
.bilan-title { font-size: 11px; font-weight: bold; color: #854F0B; text-transform: uppercase; margin-bottom: 10px; }
.bilan-grid { display: flex; gap: 10px; }
.bilan-item { flex: 1; text-align: center; }
.bilan-val { font-size: 16px; font-weight: bold; color: #1A1A1A; }
.bilan-credit { color: #0F6E56; }
.bilan-debit { color: #A32D2D; }
.bilan-label { font-size: 10px; color: #854F0B; margin-top: 2px; }
.divider { border: none; border-top: 1px solid #D3D1C7; margin: 15px 0; }
.footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #D3D1C7; display: flex; justify-content: space-between; font-size: 10px; color: #888; }
.mois-vide { padding: 8px 10px; text-align: center; color: #aaa; font-style: italic; font-size: 11px; border-top: 1px solid #e0e0e0; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="logo-badge">VIT-S</div>
        <div class="logo-sub">Services informatiques — Infogérance</div>
    </div>
    <div class="header-right">
        <div>Rapport d'interventions</div>
        <div>Généré le {{ now()->format('d/m/Y') }}</div>
    </div>
</div>

<div class="client-band">
    <div>
        <div class="client-name">{{ $contrat->client->nom_societe }}</div>
        <div class="client-meta">{{ $contrat->client->nom_signataire }} @if($contrat->client->email_signataire) · {{ $contrat->client->email_signataire }} @endif</div>
        <div class="client-meta" style="margin-top:4px">
            Contrat en cours · {{ $contrat->date_debut?->format('d/m/Y') }} – {{ $contrat->date_fin?->format('d/m/Y') }} ·
            {{ $contrat->duree_mois == 12 ? '1 an' : ($contrat->duree_mois == 24 ? '2 ans' : '3 ans') }} ·
            {{ $contrat->heures_par_periode }}h / période
        </div>
    </div>
    <div class="contrat-ref">
        <div>N° contrat</div>
        <strong>{{ $contrat->numero_contrat_vits ?? '—' }}</strong>
    </div>
</div>

@php
    $totalMinutesMoisCours = 0;
    $periodeCours = $periodesAvecInterventions->first(function($p) { return now()->between($p['debut'], $p['fin']); });
    if ($periodeCours) $totalMinutesMoisCours = $periodeCours['total_minutes'];
    $heuresConsommees = floor($totalMinutesMoisCours / 60);
    $minConsommes = $totalMinutesMoisCours % 60;
    $heuresRestantes = floor(max(0, ($contrat->heures_par_periode * 60) - $totalMinutesMoisCours) / 60);
    $minRestants = max(0, ($contrat->heures_par_periode * 60) - $totalMinutesMoisCours) % 60;
@endphp

<div class="synthese">
    <div class="syn"><div class="syn-val">{{ $contrat->heures_par_periode }}h</div><div class="syn-label">Allouées / période</div></div>
    <div class="syn"><div class="syn-val syn-val-orange">{{ $heuresConsommees }}h{{ $minConsommes > 0 ? ' '.$minConsommes.'min' : '' }}</div><div class="syn-label">Consommées (période en cours)</div></div>
    <div class="syn"><div class="syn-val syn-val-green">{{ $heuresRestantes }}h{{ $minRestants > 0 ? ' '.$minRestants.'min' : '' }}</div><div class="syn-label">Restantes (période en cours)</div></div>
</div>

<div class="section-title">Détail des interventions par période</div>

@foreach($periodesAvecInterventions as $periode)
<div class="periode-title">
    <span>Année {{ $periode['numero'] }} — {{ $periode['debut']->year }}/{{ $periode['fin']->year }}</span>
    <span style="font-size:11px;font-weight:normal;color:#666">{{ floor($periode['total_minutes']/60) }}h{{ $periode['total_minutes']%60 > 0 ? ' '.$periode['total_minutes']%60 .'min' : '' }} / {{ $periode['heures_allouees'] }}h · {{ $periode['pct'] }}%</span>
</div>

@foreach($periode['mois'] as $mois)
<div class="mois-title">
    <span>{{ $mois['label'] }}</span>
    <span style="font-weight:normal;color:#888">{{ floor($mois['total_minutes']/60) }}h{{ $mois['total_minutes']%60 > 0 ? ' '.$mois['total_minutes']%60 .'min' : '' }} consommées</span>
</div>
@if($mois['interventions']->count() > 0)
<table>
    <thead><tr><th>Date</th><th>N° bon Kizeo</th><th>Type</th><th style="text-align:right">Durée</th></tr></thead>
    <tbody>
        @foreach($mois['interventions'] as $intervention)
        <tr>
            <td>{{ $intervention->date_intervention->format('d/m/Y') }}</td>
            <td style="font-family:monospace;font-size:10px;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</td>
            <td>
                @if($intervention->type === 'site')<span class="tag-site">sur site</span>
                @elseif($intervention->type === 'distance')<span class="tag-dist">à distance</span>
                @else<span class="tag-flash">flash {{ $intervention->flash_numero }}/3</span>@endif
            </td>
            <td style="text-align:right;font-weight:bold">
                @if($intervention->type === 'flash' && $intervention->flash_numero < 3) —
                @else
                    @php $h=floor($intervention->duree_minutes/60); $m=$intervention->duree_minutes%60; @endphp
                    {{ $h > 0 ? $h.'h' : '' }}{{ $m > 0 ? ' '.$m.'min' : '' }}
                @endif
            </td>
        </tr>
        @endforeach
        <tr class="total-row"><td colspan="3">Total {{ $mois['label'] }}</td><td style="text-align:right">{{ floor($mois['total_minutes']/60) }}h{{ $mois['total_minutes']%60 > 0 ? ' '.$mois['total_minutes']%60 .'min' : '' }}</td></tr>
    </tbody>
</table>
@else
<div class="mois-vide">Aucune intervention ce mois</div>
@endif
@endforeach

<div class="bilan">
    <div class="bilan-title">Bilan période {{ $periode['numero'] }} — {{ $periode['debut']->isoFormat('MMMM YYYY') }} à {{ $periode['fin']->isoFormat('MMMM YYYY') }}</div>
    <div class="bilan-grid">
        <div class="bilan-item"><div class="bilan-val">{{ $periode['heures_allouees'] }}h</div><div class="bilan-label">Allouées</div></div>
        <div class="bilan-item"><div class="bilan-val">{{ floor($periode['total_minutes']/60) }}h{{ $periode['total_minutes']%60 > 0 ? ' '.$periode['total_minutes']%60 .'min' : '' }}</div><div class="bilan-label">Consommées</div></div>
        <div class="bilan-item">
            <div class="bilan-val {{ $periode['credit'] ? 'bilan-credit' : 'bilan-debit' }}">
                {{ $periode['credit'] ? '+' : '-' }}{{ floor(abs($periode['diff_minutes'])/60) }}h{{ abs($periode['diff_minutes'])%60 > 0 ? ' '.abs($periode['diff_minutes'])%60 .'min' : '' }}
            </div>
            <div class="bilan-label">{{ $periode['credit'] ? 'Crédit' : 'Débit' }} d'heures</div>
        </div>
        <div class="bilan-item"><div class="bilan-val">{{ $periode['pct'] }}%</div><div class="bilan-label">Taux de remplissage</div></div>
    </div>
</div>

@if(!$loop->last)<div class="divider"></div>@endif
@endforeach

<div class="footer">
    <div>VIT-S — Document confidentiel — usage client uniquement</div>
    <div>Page 1 / 1</div>
</div>

</body>
</html>
