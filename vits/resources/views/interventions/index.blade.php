@extends('layouts.app')
@section('title', 'Interventions')
@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Interventions</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $interventions->total() }} interventions</p>
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
        'n1'       => 'Année précédente',
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
    <select name="type" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:140px">
        <option value="">Tous les types</option>
        <option value="site"           {{ request('type')=='site'?'selected':'' }}>Sur site</option>
        <option value="distance"       {{ request('type')=='distance'?'selected':'' }}>À distance</option>
        <option value="flash"          {{ request('type')=='flash'?'selected':'' }}>Flash</option>
        <option value="administrateur" {{ request('type')=='administrateur'?'selected':'' }}>Administrateur</option>
    </select>
    <select name="technicien" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:160px">
        <option value="">Tous les techniciens</option>
        @foreach($techniciens as $t)
            <option value="{{ $t }}" {{ request('technicien') === $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
    </select>
    <select name="deductible" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:100px">
        <option value="">Toutes</option>
        <option value="1" {{ request('deductible')==='1'?'selected':'' }}>Déductibles</option>
        <option value="0" {{ request('deductible')==='0'?'selected':'' }}>Hors contrat</option>
    </select>
<select name="per_page" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:110px">
        <option value="20"  {{ request('per_page',20)==20?'selected':'' }}>20 / page</option>
        <option value="50"  {{ request('per_page',20)==50?'selected':'' }}>50 / page</option>
        <option value="100" {{ request('per_page',20)==100?'selected':'' }}>100 / page</option>
    </select>
    @if(request('search') || request('type') || request('technicien') || request('deductible') || request('periode'))
        <a href="{{ route('interventions.index') }}" style="font-size:13px;color:#888;padding:7px 0">Effacer</a>
    @endif
    <div style="position:relative;display:inline-block;margin-left:auto">
        <button type="button" id="btn-colonnes" onclick="togglePanelColonnes()" style="padding:7px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px;background:#fff;cursor:pointer;display:flex;align-items:center;gap:6px;color:#444">
            <i class="ti ti-columns" style="font-size:15px" aria-hidden="true"></i> Colonnes
        </button>
        <div id="panel-colonnes" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:12px;width:240px;z-index:100;box-shadow:0 4px 16px rgba(0,0,0,0.1)">
            <div style="font-size:11px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px">Affichage des colonnes</div>
            <div id="col-list-interventions"></div>
            <div style="border-top:1px solid #f0f0f0;margin-top:10px;padding-top:10px;display:flex;justify-content:space-between">
                <button type="button" onclick="resetColonnes()" style="font-size:12px;color:#888;background:none;border:none;cursor:pointer">Réinitialiser</button>
                <button type="button" onclick="appliquerColonnesEtFermer()" style="font-size:12px;background:#E8720C;color:#fff;border:none;border-radius:6px;padding:5px 12px;cursor:pointer">Appliquer</button>
            </div>
        </div>
    </div>
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
                <th class="col-date" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'date_intervention','dir'=>($sort=='date_intervention' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Date {{ $sort=='date_intervention' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-client" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'client_nom','dir'=>($sort=='client_nom' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Client {{ $sort=='client_nom' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-tech" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'technicien','dir'=>($sort=='technicien' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Technicien {{ $sort=='technicien' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th style="padding:9px 8px;border-bottom:1px solid #e0e0e0"></th>
                <th class="col-bon" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">N° Bon</th>
                <th class="col-type" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'type','dir'=>($sort=='type' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Type {{ $sort=='type' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-duree" style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'duree_minutes','dir'=>($sort=='duree_minutes' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Durée {{ $sort=='duree_minutes' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th style="padding:9px 14px;border-bottom:1px solid #e0e0e0"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($interventions as $intervention)
            @php
                $nonDed    = !$intervention->deductible;
                $manuelle  = $intervention->source_manuelle ?? false;
                $horsCtrat = $intervention->hors_contrat ?? false;
            @endphp
            <tr class="{{ $intervention->type === 'ajustement' ? 'bg-blue-50 italic' : '' }}"
                onclick="window.location='{{ route('interventions.show', $intervention) }}'"
                style="cursor:pointer;border-bottom:1px solid #f0f0f0;{{ $nonDed ? 'opacity:0.5' : '' }}"
                onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#fff'"
                data-intervention-id="{{ $intervention->id }}"
                data-commentaire="{{ e($intervention->commentaires ?? '') }}"
                data-technicien="{{ $intervention->technicien ?? '—' }}"
                data-client="{{ $intervention->client_nom ?? '—' }}"
                data-duree="{{ $intervention->duree_formatee ?? '—' }}"
                data-date="{{ $intervention->date_intervention->format('d/m/Y') }}"
                data-type="{{ $intervention->type }}">
                <td class="col-date" style="padding:10px 14px;color:#888">{{ $intervention->date_intervention->format('d/m/Y') }}{{ $intervention->heure_intervention ? ' '.$intervention->heure_intervention : '' }}</td>
                <td class="col-client" style="padding:10px 14px;font-weight:500">{{ $intervention->client_nom ?? $intervention->contrat?->client?->nom_societe ?? '—' }}</td>
                <td class="col-tech" style="padding:10px 14px;color:#555;font-size:12px">
                    @if($intervention->technicien === 'Système')
                        <span style="font-style:italic;color:#999">{{ $intervention->technicien }}</span>
                    @else
                        {{ $intervention->technicien ?? '—' }}
                    @endif
                </td>
                <td style="padding:10px 8px;width:36px">
                    @if($intervention->commentaires)
                    <button type="button"
                        onclick="ouvrirPopup(event, {{ $intervention->id }})"
                        style="width:28px;height:28px;border:1px solid #e0e0e0;border-radius:6px;background:#f5f5f5;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center"
                        title="Voir commentaire">
                        <i class="ti ti-message"></i>
                    </button>
                    @else
                    <div style="width:28px"></div>
                    @endif
                </td>
                <td class="col-bon" style="padding:10px 14px;font-family:monospace;font-size:11px;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</td>
                <td class="col-type" style="padding:10px 14px">
                    @if($intervention->type === 'site')
                        <span style="background:#E6F1FB;color:#0C447C;padding:2px 6px;border-radius:4px;font-size:10px">sur site</span>
                    @elseif($intervention->type === 'distance')
                        <span style="background:#E1F5EE;color:#085041;padding:2px 6px;border-radius:4px;font-size:10px">à distance</span>
                    @elseif($intervention->type === 'administrateur')
                        <span style="background:#F3F0FF;color:#4C1D95;padding:2px 6px;border-radius:4px;font-size:10px">admin</span>
                    @elseif($intervention->type === 'ajustement')
                        <span style="background:#F0F0F0;color:#555;padding:2px 6px;border-radius:4px;font-size:10px">{{ $intervention->duree_minutes < 0 ? 'Report n-1' : 'Dépassement n-1' }}</span>
                    @else
                        <span style="background:#FFF3E6;color:#854F0B;padding:2px 6px;border-radius:4px;font-size:10px">flash {{ $intervention->flash_numero }}/3</span>
                    @endif
                    @if($nonDed)<span style="font-size:10px;color:#777;background:#f0f0f0;border:1px solid #ddd;padding:1px 5px;border-radius:3px;margin-left:4px">Non déductible</span>@endif
                    @if($manuelle)<span style="font-size:10px;color:#b84c00;background:#fff0e8;border:1px solid #f5b48a;padding:1px 5px;border-radius:3px;margin-left:4px">Saisie manuelle</span>@endif
                </td>
                <td class="col-duree" style="padding:10px 14px;text-align:right;font-weight:500">
                    @php $h=floor($intervention->duree_minutes/60); $m=$intervention->duree_minutes%60; @endphp
                    @if($intervention->type === 'flash' && $intervention->flash_numero < 3) —
                    @else {{ $h>0?$h.'h ':'' }}{{ $m>0?$m.'min':'' }}
                    @endif
                </td>
                <td style="padding:10px 14px;text-align:right;white-space:nowrap">
                    @if($horsCtrat)
                    <button type="button" onclick="event.stopPropagation();openRattacher({{ $intervention->id }},'{{ addslashes($intervention->client_nom ?? '—') }}','{{ $intervention->date_intervention->format('d/m/Y') }}','{{ $intervention->duree_formatee }}')"
                            style="font-size:11px;color:#E8720C;background:#fff;border:1px solid #E8720C;padding:3px 8px;border-radius:6px;cursor:pointer;margin-right:4px">Rattacher</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="padding:2rem;text-align:center;color:#aaa;font-size:13px">Aucune intervention trouvée</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:10px 14px;border-top:1px solid #e0e0e0;font-size:12px;color:#888">{{ $interventions->links() }}</div>
</div>
{{-- Modal Rattacher --}}
<div id="modal-rattacher" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9998;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:1.5rem;max-width:480px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.18)">
        <div style="font-size:15px;font-weight:500;color:#1a1a1a;margin-bottom:0.75rem">Rattacher à un contrat</div>
        <div id="modal-info" style="font-size:12px;color:#888;background:#f8f8f8;border-radius:8px;padding:10px 12px;margin-bottom:1rem;line-height:1.7"></div>
        <form id="form-rattacher" method="POST">
            @csrf
            <div style="margin-bottom:1rem">
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Contrat actif à associer <span style="color:#E8720C">*</span></label>
                <select name="contrat_id" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <option value="">— Choisir un contrat —</option>
                    @foreach($contratsActifs as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->client->nom_societe }} — {{ $c->numero_contrat_vits ?? 'N/A' }}
                        ({{ $c->date_debut?->format('d/m/Y') }} → {{ $c->date_fin?->format('d/m/Y') }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px">
                <button type="button" onclick="closeRattacher()" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;background:#fff;cursor:pointer">Annuler</button>
                <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">Rattacher</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRattacher(id, client, date, duree) {
    document.getElementById('modal-info').innerHTML =
        '<strong>' + client + '</strong><br>' + date + ' · ' + duree;
    document.getElementById('form-rattacher').action = '/interventions/' + id + '/rattacher';
    var modal = document.getElementById('modal-rattacher');
    modal.style.display = 'flex';
}
function closeRattacher() {
    document.getElementById('modal-rattacher').style.display = 'none';
}
document.getElementById('modal-rattacher').addEventListener('click', function(e) {
    if (e.target === this) closeRattacher();
});
</script>

<div id="popup-commentaire" onclick="fermerPopup(event)" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.15)">
    <div id="popup-contenu" onclick="event.stopPropagation()" style="position:absolute;background:#fff;border:1px solid #e0e0e0;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.15);padding:1.25rem;width:560px;max-width:90vw">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
            <span style="font-size:13px;font-weight:500;color:#1a1a1a" id="popup-titre">Intervention</span>
            <button onclick="document.getElementById('popup-commentaire').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:18px;color:#888">×</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 16px;margin-bottom:12px;font-size:13px">
            <div style="padding:5px 0;border-bottom:1px solid #f0f0f0">
                <span style="color:#aaa;font-size:11px;display:block">Date</span>
                <span id="popup-date" style="color:#1a1a1a"></span>
            </div>
            <div style="padding:5px 0;border-bottom:1px solid #f0f0f0">
                <span style="color:#aaa;font-size:11px;display:block">Technicien</span>
                <span id="popup-technicien" style="color:#1a1a1a"></span>
            </div>
            <div style="padding:5px 0;border-bottom:1px solid #f0f0f0">
                <span style="color:#aaa;font-size:11px;display:block">Client</span>
                <span id="popup-client" style="color:#1a1a1a"></span>
            </div>
            <div style="padding:5px 0;border-bottom:1px solid #f0f0f0">
                <span style="color:#aaa;font-size:11px;display:block">Type</span>
                <span id="popup-type" style="color:#1a1a1a"></span>
            </div>
            <div style="padding:5px 0">
                <span style="color:#aaa;font-size:11px;display:block">Durée</span>
                <span id="popup-duree" style="color:#1a1a1a"></span>
            </div>
        </div>
        <div style="font-size:12px;color:#888;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px">Commentaire</div>
        <div id="popup-commentaire-texte" style="font-size:13px;color:#1a1a1a;background:#f8f8f8;border-radius:8px;padding:10px 12px;white-space:pre-wrap;max-height:200px;overflow-y:auto"></div>
    </div>
</div>

<script>
function ouvrirPopup(event, id) {
    event.stopPropagation();
    const tr = event.target.closest('tr');
    document.getElementById('popup-titre').textContent = 'Intervention — ' + tr.dataset.client;
    document.getElementById('popup-date').textContent = tr.dataset.date;
    document.getElementById('popup-client').textContent = tr.dataset.client;
    document.getElementById('popup-technicien').textContent = tr.dataset.technicien;
    document.getElementById('popup-type').textContent = tr.dataset.type;
    document.getElementById('popup-duree').textContent = tr.dataset.duree;
    document.getElementById('popup-commentaire-texte').textContent = tr.dataset.commentaire || '—';

    const popup = document.getElementById('popup-commentaire');
    popup.style.display = 'block';

    // Positionner près du bouton
    const rect = event.target.getBoundingClientRect();
    const contenu = document.getElementById('popup-contenu');
    let top = rect.bottom + 8 + window.scrollY;
    let left = rect.left;
    if (left + 560 > window.innerWidth) left = window.innerWidth - 570;
    contenu.style.top = top + 'px';
    contenu.style.left = left + 'px';
}

function fermerPopup(event) {
    document.getElementById('popup-commentaire').style.display = 'none';
}
</script>

<script>
const PAGE_INTERVENTIONS = 'interventions-colonnes';
const colonnesInterventions = [
    { id: 'col-bon',    label: 'N° Bon',     visible: true },
    { id: 'col-date',   label: 'Date',       visible: true },
    { id: 'col-client', label: 'Client',     visible: true },
    { id: 'col-tech',   label: 'Technicien', visible: true },
    { id: 'col-type',   label: 'Type',       visible: true },
    { id: 'col-duree',  label: 'Durée',      visible: true },
];

let dragSrcIndex = null;
let dragOverRow = null;

async function chargerPrefsInterventions() {
    try {
        const r = await fetch('/preferences/' + encodeURIComponent(PAGE_INTERVENTIONS));
        const data = await r.json();
        if (data && data.preferences) data.preferences.forEach(s => {
            const c = colonnesInterventions.find(x => x.id === s.id);
            if (c) c.visible = s.visible;
        });
    } catch(e) {}
    appliquerColonnesInterventions();
}

async function sauvegarderPrefsInterventions() {
    await fetch('/preferences', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ page: PAGE_INTERVENTIONS, preferences: colonnesInterventions })
    });
}

function appliquerColonnesInterventions() {
    colonnesInterventions.forEach(col => {
        document.querySelectorAll('.' + col.id).forEach(el => {
            el.style.display = col.visible ? '' : 'none';
        });
    });
}

function reorderTableDOMInterventions(colonnes) {
    const thead_tr = document.querySelector('thead tr');
    const tbody_trs = document.querySelectorAll('tbody tr');
    colonnes.forEach(col => {
        const th = thead_tr.querySelector('.' + col.id);
        if (th) thead_tr.appendChild(th);
        tbody_trs.forEach(tr => {
            const td = tr.querySelector('.' + col.id);
            if (td) tr.appendChild(td);
        });
    });
}

function appliquerColonnesEtFermer() {
    appliquerColonnesInterventions();
    reorderTableDOMInterventions(colonnesInterventions);
    sauvegarderPrefsInterventions();
    document.getElementById('panel-colonnes').style.display = 'none';
}

function renderPanelColonnes() {
    const list = document.getElementById('col-list-interventions');
    list.innerHTML = '';
    colonnesInterventions.forEach((col, i) => {
        const row = document.createElement('div');
        row.draggable = true;
        row.dataset.index = i;
        row.style = 'display:flex;align-items:center;gap:8px;padding:6px 4px;border-radius:6px;';
        row.innerHTML = `
            <i class="ti ti-grip-vertical" style="font-size:14px;color:#ccc;cursor:grab" aria-hidden="true"></i>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;font-size:13px;color:#1a1a1a">
                <input type="checkbox" ${col.visible ? 'checked' : ''} onchange="toggleColonneInter(${i}, this.checked)" style="accent-color:#E8720C;width:15px;height:15px;">
                ${col.label}
            </label>
        `;
        row.addEventListener('dragstart', function(e) {
            e.stopPropagation();
            dragSrcIndex = i;
            e.dataTransfer.effectAllowed = 'move';
        });
        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (dragOverRow && dragOverRow !== row) dragOverRow.style.background = '';
            row.style.background = '#f5f5f5';
            dragOverRow = row;
        });
        row.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const destIndex = i;
            if (dragSrcIndex !== null && dragSrcIndex !== destIndex) {
                const [moved] = colonnesInterventions.splice(dragSrcIndex, 1);
                colonnesInterventions.splice(destIndex, 0, moved);
            }
            renderPanelColonnes();
        });
        row.addEventListener('dragend', function(e) {
            e.stopPropagation();
            if (dragOverRow) dragOverRow.style.background = '';
            dragOverRow = null;
            dragSrcIndex = null;
        });
        list.appendChild(row);
    });
}

function toggleColonneInter(i, checked) {
    colonnesInterventions[i].visible = checked;
    sauvegarderPrefsInterventions();
    appliquerColonnesInterventions();
}

function resetColonnes() {
    colonnesInterventions.forEach(c => c.visible = true);
    renderPanelColonnes();
    sauvegarderPrefsInterventions();
    appliquerColonnesInterventions();
}

function togglePanelColonnes() {
    const p = document.getElementById('panel-colonnes');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
    if (p.style.display === 'block') renderPanelColonnes();
}

document.addEventListener('click', function(e) {
    const panel = document.getElementById('panel-colonnes');
    const btn = document.getElementById('btn-colonnes');
    if (panel && btn && !panel.contains(e.target) && !btn.contains(e.target)) panel.style.display = 'none';
});

chargerPrefsInterventions();
</script>
@endsection
