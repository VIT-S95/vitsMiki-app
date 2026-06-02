@extends('layouts.app')
@section('title', 'Interventions')
@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Interventions</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $interventions->total() }} interventions — triées par date décroissante</p>
    </div>
</div>

<form method="GET" id="filter-form">
@php
    $periodeActive = request('periode', '');
    $periodesLabels = [
        ''         => 'Tout',
        'today'    => "Aujourd'hui",
        'week'     => 'Cette semaine',
        'month'    => 'Ce mois',
        'semestre' => 'Dernier semestre',
        'annee'    => 'Cette année',
        'n1'       => 'N-1',
        'custom'   => 'Personnalisé',
    ];
@endphp

{{-- Filtres rapides période --}}
<div style="display:flex;gap:6px;margin-bottom:0.6rem;flex-wrap:wrap">
    @foreach($periodesLabels as $val => $label)
    @php $isActive = ($periodeActive === $val); @endphp
    <button type="button" onclick="setPeriode('{{ $val }}')"
        style="padding:5px 11px;border-radius:6px;font-size:12px;cursor:pointer;border:1px solid {{ $isActive ? '#E8720C' : '#ddd' }};background:{{ $isActive ? '#E8720C' : '#f5f5f5' }};color:{{ $isActive ? '#fff' : '#555' }}">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- Champs date personnalisée --}}
<div id="custom-dates" style="display:{{ $periodeActive === 'custom' ? 'flex' : 'none' }};gap:8px;align-items:center;margin-bottom:0.6rem">
    <input type="date" name="date_debut" value="{{ request('date_debut') }}"
        style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
    <span style="color:#aaa;font-size:13px">→</span>
    <input type="date" name="date_fin" value="{{ request('date_fin') }}"
        style="padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
    <button type="submit" style="padding:6px 12px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer">Appliquer</button>
</div>

<input type="hidden" name="periode" id="periode-input" value="{{ $periodeActive }}">

{{-- Filtres texte / type --}}
<div style="display:flex;gap:8px;margin-bottom:1rem;flex-wrap:wrap">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client…" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;width:250px">
    <select name="type" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        <option value="">Tous les types</option>
        <option value="site"           {{ request('type')=='site'?'selected':'' }}>Sur site</option>
        <option value="distance"       {{ request('type')=='distance'?'selected':'' }}>À distance</option>
        <option value="flash"          {{ request('type')=='flash'?'selected':'' }}>Flash</option>
        <option value="administrateur" {{ request('type')=='administrateur'?'selected':'' }}>Administrateur</option>
    </select>
    <select name="deductible" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        <option value="">Toutes</option>
        <option value="1" {{ request('deductible')==='1'?'selected':'' }}>Déductibles</option>
        <option value="0" {{ request('deductible')==='0'?'selected':'' }}>Hors contrat</option>
    </select>
    <button type="submit" style="padding:7px 12px;background:#f5f5f5;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">Filtrer</button>
    <select name="per_page" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        <option value="20"  {{ request('per_page',20)==20?'selected':'' }}>20 / page</option>
        <option value="50"  {{ request('per_page',20)==50?'selected':'' }}>50 / page</option>
        <option value="100" {{ request('per_page',20)==100?'selected':'' }}>100 / page</option>
    </select>
    @if(request('search') || request('type') || request('deductible') || request('periode'))
        <a href="{{ route('interventions.index') }}" style="font-size:13px;color:#888;padding:7px 0">Effacer</a>
    @endif
</div>
</form>

<script>
function setPeriode(val) {
    document.getElementById('periode-input').value = val;
    document.getElementById('custom-dates').style.display = val === 'custom' ? 'flex' : 'none';
    if (val !== 'custom') {
        var dd = document.querySelector('[name=date_debut]');
        var df = document.querySelector('[name=date_fin]');
        if (dd) dd.value = '';
        if (df) df.value = '';
        document.getElementById('filter-form').submit();
    }
}
</script>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead style="background:#f5f5f5">
            <tr>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Date</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Client</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Technicien</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">N° Bon</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Type</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Durée</th>
                <th style="padding:9px 14px;border-bottom:1px solid #e0e0e0"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($interventions as $intervention)
            @php $nonDed = !$intervention->deductible; @endphp
            <tr onclick="window.location='{{ route('contrats.show', $intervention->contrat) }}'"
                style="cursor:pointer;border-bottom:1px solid #f0f0f0;{{ $nonDed ? 'opacity:0.5' : '' }}"
                onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#fff'">
                <td style="padding:10px 14px;color:#888">
                    {{ $intervention->date_intervention->format('d/m/Y') }}
                    @if($intervention->heure_intervention)
                        <span style="font-size:11px;color:#bbb;display:block">{{ $intervention->heure_intervention }}</span>
                    @endif
                </td>
                <td style="padding:10px 14px;font-weight:500">{{ $intervention->contrat->client->nom_societe ?? '—' }}</td>
                <td style="padding:10px 14px;color:#555;font-size:12px">{{ $intervention->technicien ?? '—' }}</td>
                <td style="padding:10px 14px;font-family:monospace;font-size:11px;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</td>
                <td style="padding:10px 14px">
                    @if($intervention->type === 'site')
                        <span style="background:#E6F1FB;color:#0C447C;padding:2px 6px;border-radius:4px;font-size:10px">sur site</span>
                    @elseif($intervention->type === 'distance')
                        <span style="background:#E1F5EE;color:#085041;padding:2px 6px;border-radius:4px;font-size:10px">à distance</span>
                    @elseif($intervention->type === 'administrateur')
                        <span style="background:#F3F0FF;color:#4C1D95;padding:2px 6px;border-radius:4px;font-size:10px">admin</span>
                    @else
                        <span style="background:#FFF3E6;color:#854F0B;padding:2px 6px;border-radius:4px;font-size:10px">flash {{ $intervention->flash_numero }}/3</span>
                    @endif
                    @if($nonDed)<span style="font-size:10px;color:#aaa;margin-left:4px">hors contrat</span>@endif
                </td>
                <td style="padding:10px 14px;text-align:right;font-weight:500">
                    @php $h=floor($intervention->duree_minutes/60); $m=$intervention->duree_minutes%60; @endphp
                    @if($intervention->type === 'flash' && $intervention->flash_numero < 3) —
                    @else {{ $h>0?$h.'h ':'' }}{{ $m>0?$m.'min':'' }}
                    @endif
                </td>
                <td style="padding:10px 14px;text-align:right">
                    <a href="{{ route('interventions.edit', $intervention) }}" onclick="event.stopPropagation()" style="font-size:12px;color:#888;text-decoration:none;padding:4px 8px;border:1px solid #ddd;border-radius:6px">✏</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="padding:2rem;text-align:center;color:#aaa;font-size:13px">Aucune intervention trouvée</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:10px 14px;border-top:1px solid #e0e0e0;font-size:12px;color:#888">{{ $interventions->links() }}</div>
</div>
@endsection
