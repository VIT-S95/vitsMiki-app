@extends('layouts.app')
@section('title', 'Performance')
@section('content')

@php
    // $periodeActive est injecté par le contrôleur (défaut 'week')
    $periodesLabels     = [
        ''         => 'Tout',
        'today'    => "Aujourd'hui",
        'week'     => 'Cette semaine',
        'month'    => 'Ce mois',
        'semestre' => 'Dernier semestre',
        'annee'    => 'Cette année',
        'n1'       => 'Année précédente',
        'custom'   => 'Personnalisé',
    ];
    $totalNb          = $statsByTech->sum('nb');
    $totalMinutes     = $statsByTech->sum('minutes');
    $th = intdiv($totalMinutes, 60);
    $tm = $totalMinutes % 60;
    $totalHeuresLabel = $tm > 0 ? "{$th}h {$tm}min" : "{$th}h";
@endphp

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Performance techniciens</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $totalNb }} interventions · {{ $totalHeuresLabel }} dans la période</p>
    </div>
</div>

<form method="GET" id="filter-form">

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

<div style="display:flex;gap:8px;margin-bottom:1rem;flex-wrap:wrap">
    <select name="technicien" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:160px">
        <option value="">Tous les techniciens</option>
        @foreach($techniciens as $t)
            <option value="{{ $t }}" {{ request('technicien') === $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
    </select>
    @if(request('technicien') || request()->query('periode'))
        <a href="{{ route('performance.index') }}" style="font-size:13px;color:#888;padding:7px 0">Effacer</a>
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

@if($statsByTech->isEmpty())
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:3rem;text-align:center;color:#aaa;font-size:13px">
    Aucune intervention trouvée pour cette période.
</div>
@else

{{-- Pills techniciens --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:0.75rem">
    @foreach($statsByTech as $tech => $stats)
    <span onclick="toggleTech(this)" data-tech="{{ $tech }}" data-active="1"
          style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid #E8720C;background:#fff;color:#E8720C;font-weight:500;user-select:none">
        {{ $tech }}
    </span>
    @endforeach
</div>

{{-- Tableau récapitulatif --}}
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden;margin-bottom:1rem">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead style="background:#f5f5f5">
            <tr>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Technicien</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Heures</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Interventions</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Sur site</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">À distance</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Flash</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statsByTech as $tech => $stats)
            <tr data-tech="{{ $tech }}" style="border-bottom:1px solid #f0f0f0">
                <td style="padding:10px 14px;font-weight:500">{{ $tech }}</td>
                <td style="padding:10px 14px;text-align:right;font-weight:600;color:#E8720C">{{ $stats['heures_label'] }}</td>
                <td style="padding:10px 14px;text-align:right">{{ $stats['nb'] }}</td>
                <td style="padding:10px 14px;text-align:right">
                    @if($stats['site'])<span style="background:#E6F1FB;color:#0C447C;padding:2px 8px;border-radius:4px;font-size:12px">{{ $stats['site'] }}</span>@else —@endif
                </td>
                <td style="padding:10px 14px;text-align:right">
                    @if($stats['distance'])<span style="background:#E1F5EE;color:#085041;padding:2px 8px;border-radius:4px;font-size:12px">{{ $stats['distance'] }}</span>@else —@endif
                </td>
                <td style="padding:10px 14px;text-align:right">
                    @if($stats['flash'])<span style="background:#FFF3E6;color:#854F0B;padding:2px 8px;border-radius:4px;font-size:12px">{{ $stats['flash'] }}</span>@else —@endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Graphiques --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
    <div style="flex:1;min-width:280px;background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">Heures par technicien</div>
        <canvas id="chart-barres"></canvas>
    </div>
    <div style="flex:1;min-width:280px;background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.25rem">
        <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">Évolution mensuelle (interventions)</div>
        <canvas id="chart-courbes"></canvas>
    </div>
</div>

<script>
(function() {
    const techLabels        = @json($techLabels);
    const techHeuresOrig    = @json($techHeures);
    const techHeures        = [...techHeuresOrig];
    const moisLabels        = @json($moisLabels);
    const evolutionDatasets = @json($evolutionDatasets);

    function fmtHeures(raw) {
        if (raw === null || raw === undefined) return '';
        const totalMin = Math.round(raw * 60);
        const h = Math.floor(totalMin / 60);
        const m = totalMin % 60;
        return m > 0 ? h + 'h ' + m + 'min' : h + 'h';
    }

    const chartBarres = new Chart(document.getElementById('chart-barres'), {
        type: 'bar',
        data: {
            labels: techLabels,
            datasets: [{
                label: 'Heures',
                data: techHeures,
                backgroundColor: techLabels.map(() => '#E8720C'),
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) { return fmtHeures(context.raw); }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 11 } } },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });

    let chartCourbes = null;
    if (moisLabels.length > 0) {
        chartCourbes = new Chart(document.getElementById('chart-courbes'), {
            type: 'line',
            data: { labels: moisLabels, datasets: evolutionDatasets },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 12 } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } },
                    x: { ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    window.toggleTech = function(pill) {
        const tech      = pill.dataset.tech;
        const isActive  = pill.dataset.active === '1';
        const nowActive = !isActive;
        const idx       = techLabels.indexOf(tech);

        pill.dataset.active = nowActive ? '1' : '0';
        if (nowActive) {
            pill.style.borderColor = '#E8720C';
            pill.style.background  = '#fff';
            pill.style.color       = '#E8720C';
            pill.style.fontWeight  = '500';
        } else {
            pill.style.borderColor = '#ddd';
            pill.style.background  = '#f5f5f5';
            pill.style.color       = '#aaa';
            pill.style.fontWeight  = 'normal';
        }

        const row = document.querySelector('tr[data-tech="' + tech + '"]');
        if (row) row.style.display = nowActive ? '' : 'none';

        if (idx !== -1) {
            chartBarres.data.datasets[0].data[idx] = nowActive ? techHeuresOrig[idx] : null;
            chartBarres.update();
        }

        if (chartCourbes) {
            const dsIdx = chartCourbes.data.datasets.findIndex(ds => ds.label === tech);
            if (dsIdx !== -1) {
                chartCourbes.setDatasetVisibility(dsIdx, nowActive);
                chartCourbes.update();
            }
        }
    };
})();
</script>

@endif
@endsection
