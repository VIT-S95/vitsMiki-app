<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
img { border: 0 !important; outline: 0 !important; }
body { font-family: Arial, sans-serif; font-size: 12px; color: #2C2C2A; margin: 20px 25px; }
.header { padding-bottom: 7px; border-bottom: 2px solid #E8720C; margin-bottom: 16px; }
.logo-badge { background: #E8720C; color: #fff; font-size: 16px; font-weight: bold; padding: 5px 14px; border-radius: 4px; display: inline-block; }
.logo-sub { font-size: 11px; color: #888; margin-top: 4px; }
.header-right { text-align: right; font-size: 11px; color: #888; }
.client-band { background: #F1EFE8; border-radius: 4px; padding: 12px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; }
.client-name { font-size: 15px; font-weight: bold; color: #1A1A1A; }
.client-meta { font-size: 11px; color: #5F5E5A; margin-top: 3px; }
.contrat-ref { text-align: right; font-size: 11px; color: #888; }
.contrat-ref strong { display: block; font-size: 12px; color: #444; font-family: monospace; }
.synthese { display: flex; gap: 0; margin-bottom: 20px; border: 1px solid #D3D1C7; border-radius: 4px; overflow: hidden; }
.syn { flex: 1; padding: 10px; text-align: center; border-right: 1px solid #D3D1C7; }
.syn:last-child { border-right: none; }
.syn-val { font-size: 16px; font-weight: bold; color: #1A1A1A; }
.syn-val-orange { color: #E8720C; }
.syn-val-green { color: #0F6E56; }
.syn-label { font-size: 10px; color: #888; margin-top: 2px; }
.section-title { font-size: 10px; font-weight: bold; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; margin-top: 20px; }
.periode-title { display: flex; justify-content: space-between; background: #F1EFE8; padding: 6px 10px; border-radius: 4px 4px 0 0; font-size: 12px; font-weight: bold; }
.mois-title { display: flex; justify-content: space-between; padding: 5px 10px; background: #fafafa; border-top: 1px solid #e0e0e0; font-size: 11px; font-weight: bold; color: #444; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
thead { display: table-header-group; }
th { padding: 5px 8px; text-align: left; font-size: 10px; color: #888; text-transform: uppercase; border-bottom: 1px solid #D3D1C7; background: #fff; }
td { padding: 5px 8px; border-bottom: 1px solid #F1EFE8; color: #444; }
tr { page-break-inside: avoid; }
.mois-block { page-break-inside: avoid; }
.tag-site { background: #E6F1FB; color: #0C447C; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.tag-dist { background: #E1F5EE; color: #085041; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.tag-flash { background: #FFF3E6; color: #854F0B; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.tag-admin { background: #F3F0FF; color: #4C1D95; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.tag-ajustement { background: #F0F0F0; color: #555; padding: 1px 5px; border-radius: 3px; font-size: 10px; }
.row-ajustement { background: #EFF6FF; font-style: italic; }
.technicien-systeme { font-style: italic; color: #999; }
.total-row td { background: #F1EFE8; font-weight: bold; }
.divider { border: none; border-top: 1px solid #D3D1C7; margin: 15px 0; }
.footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #D3D1C7; display: flex; justify-content: space-between; font-size: 10px; color: #888; }
</style>
</head>
<body>

<div class="header">
    @if(file_exists(public_path('storage/logo/logo.png')))
    <img src="{{ public_path('storage/logo/logo.png') }}" style="max-height:40px;width:auto;border:0;outline:0;box-shadow:none;display:block;margin-bottom:4px">
    @else
    <div class="logo-badge" style="margin-bottom:4px">VIT-S</div>
    @endif
    <div class="logo-sub">Services informatiques — Infogérance</div>
    <div class="logo-sub">Rapport d'interventions — Généré le {{ now()->format('d/m/Y') }}</div>
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
    $premiereP = $periodesAvecInterventions->first();
    $totalMinutesCours = $premiereP ? $premiereP['total_minutes'] : 0;
    $hCons  = intdiv($totalMinutesCours, 60);
    $mCons  = $totalMinutesCours - ($hCons * 60);
    $diffMin = $premiereP ? $premiereP['diff_minutes'] : 0;
    $restMin = abs($diffMin);
    $hRest  = intdiv($restMin, 60);
    $mRest  = $restMin - ($hRest * 60);
    $enDepassement = $premiereP ? !$premiereP['credit'] : false;
    $periodeCoursFinie = $premiereP ? $premiereP['periode_finie'] : false;
    $labelConsommees = $periodeCoursFinie ? 'Consommées (période terminée)' : 'Consommées (période en cours)';
    $labelRestantes  = $enDepassement ? 'Dépassement' : ($periodeCoursFinie ? 'Restantes (période terminée)' : 'Restantes (période en cours)');
@endphp

@if(true)
<table style="width:100%;border-collapse:collapse;margin-bottom:20px;border:1px solid #D3D1C7;border-radius:4px">
    <tr>
        <td style="width:33%;padding:10px;text-align:center;border-right:1px solid #D3D1C7">
            <div style="font-size:16px;font-weight:bold;color:#1A1A1A">{{ $contrat->heures_par_periode }}h</div>
            <div style="font-size:10px;color:#888;margin-top:2px">Allouées / période</div>
        </td>
        <td style="width:33%;padding:10px;text-align:center;border-right:1px solid #D3D1C7">
            <div style="font-size:16px;font-weight:bold;color:#E8720C">{{ $hCons }}h{{ $mCons > 0 ? ' '.$mCons.'min' : '' }}</div>
            <div style="font-size:10px;color:#888;margin-top:2px">{{ $labelConsommees }}</div>
        </td>
        <td style="width:33%;padding:10px;text-align:center">
            <div style="font-size:16px;font-weight:bold;color:{{ $enDepassement ? '#A32D2D' : '#0F6E56' }}">{{ $hRest }}h{{ $mRest > 0 ? ' '.$mRest.'min' : '' }}</div>
            <div style="font-size:10px;color:#888;margin-top:2px">{{ $labelRestantes }}</div>
        </td>
    </tr>
</table>
@endif

<div class="section-title">Détail des interventions par période</div>

@foreach($periodesAvecInterventions as $periode)
@php
    $pMin  = $periode['total_minutes'];
    $pH    = intdiv($pMin, 60);
    $pM    = $pMin - ($pH * 60);
    $dMin  = abs($periode['diff_minutes']);
    $dH    = intdiv($dMin, 60);
    $dM    = $dMin - ($dH * 60);
    $isCredit = $periode['credit'];
@endphp
@if(!$loop->first)
<div style="page-break-before: always;"></div>

<div class="header">
    @if(file_exists(public_path('storage/logo/logo.png')))
    <img src="{{ public_path('storage/logo/logo.png') }}" style="max-height:40px;width:auto;border:0;outline:0;box-shadow:none;display:block;margin-bottom:4px">
    @else
    <div class="logo-badge" style="margin-bottom:4px">VIT-S</div>
    @endif
    <div class="logo-sub">Services informatiques — Infogérance</div>
    <div class="logo-sub">Rapport d'interventions — Généré le {{ now()->format('d/m/Y') }}</div>
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
    $periodeEnCours     = !$periode['periode_finie'];
    $periodeConsCouleur = $periodeEnCours ? '#E8720C' : ($isCredit ? '#0F6E56' : '#A32D2D');
    $periodeRestLabel   = $isCredit ? 'Restantes' : 'Dépassement';
    $periodeRestCouleur = $isCredit ? '#0F6E56' : '#A32D2D';
    $pHCons = intdiv($periode['total_minutes'], 60);
    $pMCons = $periode['total_minutes'] % 60;
    $pDiff  = abs($periode['diff_minutes']);
    $pHDiff = intdiv($pDiff, 60);
    $pMDiff = $pDiff % 60;
@endphp
<table style="width:100%;border-collapse:collapse;margin-bottom:20px;border:1px solid #D3D1C7;border-radius:4px">
    <tr>
        <td style="width:33%;padding:10px;text-align:center;border-right:1px solid #D3D1C7">
            <div style="font-size:16px;font-weight:bold;color:#1A1A1A">{{ $periode['heures_allouees'] }}h</div>
            <div style="font-size:10px;color:#888;margin-top:2px">Allouées</div>
        </td>
        <td style="width:33%;padding:10px;text-align:center;border-right:1px solid #D3D1C7">
            <div style="font-size:16px;font-weight:bold;color:{{ $periodeConsCouleur }}">{{ $pHCons }}h{{ $pMCons > 0 ? ' '.$pMCons.'min' : '' }}</div>
            <div style="font-size:10px;color:#888;margin-top:2px">Consommées</div>
        </td>
        <td style="width:33%;padding:10px;text-align:center">
            <div style="font-size:16px;font-weight:bold;color:{{ $periodeRestCouleur }}">{{ $pHDiff }}h{{ $pMDiff > 0 ? ' '.$pMDiff.'min' : '' }}</div>
            <div style="font-size:10px;color:#888;margin-top:2px">{{ $periodeRestLabel }}</div>
        </td>
    </tr>
</table>
@endif
<div class="periode-title">
    <span>Année {{ $periode['numero'] }} — {{ $periode['debut']->format('d/m/Y') }} / {{ $periode['fin']->format('d/m/Y') }}</span>
    <span style="font-size:11px;font-weight:normal;color:#666">
        {{ $pH }}h{{ $pM > 0 ? ' '.$pM.'min' : '' }} / {{ $periode['heures_allouees'] }}h · {{ $periode['pct'] }}%
    </span>
</div>

@if($loop->first && !empty($interventionsAnterieures) && $interventionsAnterieures->isNotEmpty())
@php
    $minAntPdf   = $interventionsAnterieures->sum('duree_minutes');
    $hAntPdf     = intdiv($minAntPdf, 60);
    $mAntPdf     = $minAntPdf % 60;
    $labelAntPdf = ($hAntPdf > 0 ? $hAntPdf.'h' : '').($hAntPdf > 0 && $mAntPdf > 0 ? ' ' : '').($mAntPdf > 0 ? $mAntPdf.'min' : '');
    $nbAntPdf    = $interventionsAnterieures->count();
@endphp
<div style="border:2px dashed #E8720C;border-radius:4px;background:#FFF8F3;padding:8px 10px;margin:8px 0">
    <div style="font-size:11px;font-weight:bold;color:#E8720C;margin-bottom:3px">Interventions antérieures au contrat</div>
    <div style="font-size:10px;color:#aaa;margin-bottom:6px">Rattachées manuellement — comptabilisées dans cette période</div>
    <table style="width:100%;border-collapse:collapse;font-size:11px">
        <thead>
            <tr style="border-bottom:1px solid #f0e0d0">
                <th style="width:18%;padding:3px 6px">Date</th>
                <th style="width:25%;padding:3px 6px">Technicien</th>
                <th style="width:35%;padding:3px 6px">Type</th>
                <th style="width:22%;padding:3px 6px;text-align:right">Durée</th>
            </tr>
        </thead>
        <tbody>
            @foreach($interventionsAnterieures as $i)
            @php $hiA=intdiv($i->duree_minutes,60); $miA=$i->duree_minutes%60; @endphp
            <tr style="border-top:1px solid #f5ede5" class="{{ $i->type === 'ajustement' ? 'row-ajustement' : '' }}">
                <td style="padding:3px 6px;color:#888">{{ $i->date_intervention->format('d/m/Y') }}</td>
                <td style="padding:3px 6px;color:#555">
                    @if($i->technicien === 'Système')<span class="technicien-systeme">{{ $i->technicien }}</span>
                    @else{{ $i->technicien ?? '—' }}@endif
                </td>
                <td style="padding:3px 6px">
                    @if($i->type==='site')<span class="tag-site">sur site</span>
                    @elseif($i->type==='distance')<span class="tag-dist">à distance</span>
                    @elseif($i->type==='administrateur')<span class="tag-admin">admin</span>
                    @elseif($i->type==='ajustement')<span class="tag-ajustement">{{ $i->duree_minutes < 0 ? 'Report n-1' : 'Dépassement n-1' }}</span>
                    @else<span class="tag-flash">flash {{ $i->flash_numero }}/3</span>@endif
                </td>
                <td style="padding:3px 6px;text-align:right;font-weight:bold">{{ $hiA>0?$hiA.'h ':'' }}{{ $miA>0?$miA.'min':'' }}</td>
            </tr>
            @endforeach
            <tr style="border-top:2px solid #f0e0d0;background:#fff0e8">
                <td colspan="4" style="padding:4px 6px;font-size:10px;font-weight:bold;color:#E8720C">
                    Total : {{ $labelAntPdf }} ({{ $nbAntPdf }} intervention{{ $nbAntPdf > 1 ? 's' : '' }})
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endif

@foreach($periode['mois'] as $mois)
@php
    $mMin = $mois['total_minutes'];
    $mH   = intdiv($mMin, 60);
    $mM   = $mMin - ($mH * 60);
@endphp
@if(!$mois['is_future'])
<div class="mois-block">
<div class="mois-title">
    <span>{{ $mois['label'] }}</span>
</div>
<table style="table-layout:fixed;width:100%">
    <thead>
        <tr>
            <th style="width:18%">Date</th>
            <th style="width:22%">N° Intervention</th>
            <th style="width:18%">Technicien</th>
            <th style="width:22%">Type</th>
            <th style="width:20%;text-align:right">Durée</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mois['interventions'] as $intervention)
        @php
            $iH = intdiv($intervention->duree_minutes, 60);
            $iM = $intervention->duree_minutes - ($iH * 60);
            $iHabs = intdiv(abs($intervention->duree_minutes), 60);
            $iMabs = abs($intervention->duree_minutes) - ($iHabs * 60);
        @endphp
        <tr class="{{ $intervention->type === 'ajustement' ? 'row-ajustement' : '' }}">
            <td>{{ $intervention->date_intervention->format('d/m/Y') }}</td>
            <td style="font-family:monospace;font-size:10px;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</td>
            <td>
                @if($intervention->technicien === 'Système')<span class="technicien-systeme">{{ $intervention->technicien }}</span>
                @else{{ $intervention->technicien ?? '—' }}@endif
            </td>
            <td>
                @if($intervention->type === 'site')<span class="tag-site">sur site</span>
                @elseif($intervention->type === 'distance')<span class="tag-dist">à distance</span>
                @elseif($intervention->type === 'administrateur')<span class="tag-admin">admin</span>
                @elseif($intervention->type === 'ajustement')<span class="tag-ajustement">{{ $intervention->duree_minutes < 0 ? 'Report n-1' : 'Dépassement n-1' }}</span>
                @else<span class="tag-flash">flash {{ $intervention->flash_numero }}/3</span>@endif
            </td>
            <td style="text-align:right;font-weight:bold">
                @if($intervention->type === 'flash' && $intervention->flash_numero < 3)
                    —
                @elseif($intervention->duree_minutes < 0)
                    <span style="color:#0F6E56">+{{ $iHabs > 0 ? $iHabs.'h ' : '' }}{{ $iMabs > 0 ? $iMabs.'min' : '' }}</span>
                @else
                    {{ $iH > 0 ? $iH.'h ' : '' }}{{ $iM > 0 ? $iM.'min' : '' }}
                @endif
            </td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="4">Total {{ $mois['label'] }}</td>
            <td style="text-align:right">{{ $mH }}h{{ $mM > 0 ? ' '.$mM.'min' : '' }}</td>
        </tr>
    </tbody>
</table>
</div>
@endif
@endforeach

@if($periode['periode_finie'])
@php
    $bilanBg     = $isCredit ? '#f0faf5' : '#fff0f0';
    $bilanBorder = $isCredit ? '#9fe1cb' : '#e2b4b4';
    $bilanTitre  = $isCredit ? '#0f6e56' : '#a32d2d';
    $bilanValeur = $isCredit ? '#085041' : '#791f1f';
    $bilanIcone  = $isCredit ? '✓' : '⚠';
    $bilanStatut = $isCredit ? 'Crédit' : 'Dépassement';
    $bilanColTitre = $isCredit ? 'Restantes' : 'Dépassement';
@endphp
<div style="background: {{ $bilanBg }}; border: 1px solid {{ $bilanBorder }}; border-radius: 6px; padding: 12px 16px; margin-top: 16px; margin-bottom: 16px;">
    <div style="font-size: 11px; font-weight: bold; color: {{ $bilanTitre }}; text-transform: uppercase; margin-bottom: 8px;">
        {{ $bilanIcone }} Bilan période {{ $periode['numero'] }} — {{ $periode['debut']->locale('fr')->isoFormat('MMMM YYYY') }} à {{ $periode['fin']->locale('fr')->isoFormat('MMMM YYYY') }} · {{ $bilanStatut }}
    </div>
    <table style="width: 100%; border-collapse: collapse;">
        <thead style="background: transparent;">
            <tr>
                <th style="font-weight: normal; text-transform: none; font-size: 10px; color: {{ $bilanTitre }}; padding: 5px 8px; border-bottom: 1px solid {{ $bilanBorder }}; text-align: left; background: transparent;">Allouées</th>
                <th style="font-weight: normal; text-transform: none; font-size: 10px; color: {{ $bilanTitre }}; padding: 5px 8px; border-bottom: 1px solid {{ $bilanBorder }}; text-align: left; background: transparent;">Consommées</th>
                <th style="font-weight: normal; text-transform: none; font-size: 10px; color: {{ $bilanTitre }}; padding: 5px 8px; border-bottom: 1px solid {{ $bilanBorder }}; text-align: left; background: transparent;">{{ $bilanColTitre }}</th>
                <th style="font-weight: normal; text-transform: none; font-size: 10px; color: {{ $bilanTitre }}; padding: 5px 8px; border-bottom: 1px solid {{ $bilanBorder }}; text-align: right; background: transparent;">Taux</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 8px; font-size: 16px; font-weight: bold; color: {{ $bilanValeur }};">{{ $periode['heures_allouees'] }}h</td>
                <td style="padding: 8px; font-size: 16px; font-weight: bold; color: {{ $bilanValeur }};">{{ $pH }}h{{ $pM > 0 ? ' '.$pM.'min' : '' }}</td>
                <td style="padding: 8px; font-size: 16px; font-weight: bold; color: {{ $bilanValeur }};">{{ $dH }}h{{ $dM > 0 ? ' '.$dM.'min' : '' }}</td>
                <td style="padding: 8px; font-size: 16px; font-weight: bold; color: {{ $bilanValeur }}; text-align: right;">{{ $periode['pct'] }}%</td>
            </tr>
        </tbody>
    </table>
</div>

@endif
@if(!$loop->last)<div class="divider"></div>@endif
@endforeach

<div class="footer">
    <div>VIT-S — Document confidentiel — usage client uniquement</div>
    <div>Généré le {{ now()->format('d/m/Y à H:i') }}</div>
</div>

</body>
</html>