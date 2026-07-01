@extends('layouts.app')
@section('title', 'Contrats')
@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Contrats</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $contrats->total() }} contrats</p>
    </div>
    <a href="{{ route('contrats.create') }}" style="padding:7px 14px;background:#E8720C;color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none">+ Nouveau contrat</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

<form method="GET" style="display:flex;gap:8px;margin-bottom:1rem">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client…" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;width:250px">
    <select name="statut" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        <option value="tous" {{ request('statut')=='tous'?'selected':'' }}>Tous les statuts</option>
        <option value="en-cours" {{ (request('statut','en-cours')=='en-cours')?'selected':'' }}>En cours</option>
        <option value="expire" {{ request('statut')=='expire'?'selected':'' }}>Expirés</option>
        <option value="non-actif" {{ request('statut')=='non-actif'?'selected':'' }}>Non actifs</option>
    </select>
    <button type="submit" style="padding:7px 12px;background:#f5f5f5;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">Filtrer</button>
    <select name="per_page" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:110px">
        <option value="20" {{ request('per_page',20)==20?'selected':'' }}>20 / page</option>
        <option value="50" {{ request('per_page',20)==50?'selected':'' }}>50 / page</option>
        <option value="100" {{ request('per_page',20)==100?'selected':'' }}>100 / page</option>
    </select>
    @if(request('search') || request('statut'))<a href="{{ route('contrats.index') }}" style="font-size:13px;color:#888;padding:7px 0">Effacer</a>@endif
    <div style="position:relative;display:inline-block;margin-left:auto">
        <button type="button" id="btn-colonnes" onclick="togglePanelColonnesContrats()" style="padding:7px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px;background:#fff;cursor:pointer;display:flex;align-items:center;gap:6px;color:#444">
            <i class="ti ti-columns" style="font-size:15px" aria-hidden="true"></i> Colonnes
        </button>
        <div id="panel-colonnes" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:12px;width:240px;z-index:100;box-shadow:0 4px 16px rgba(0,0,0,0.1)">
            <div style="font-size:11px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px">Affichage des colonnes</div>
            <div id="col-list-contrats"></div>
            <div style="border-top:1px solid #f0f0f0;margin-top:10px;padding-top:10px;display:flex;justify-content:space-between">
                <button type="button" onclick="resetColonnesContrats()" style="font-size:12px;color:#888;background:none;border:none;cursor:pointer">Réinitialiser</button>
                <button type="button" onclick="appliquerColonnesEtFermerContrats()" style="font-size:12px;background:#E8720C;color:#fff;border:none;border-radius:6px;padding:5px 12px;cursor:pointer">Appliquer</button>
            </div>
        </div>
    </div>
</form>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead style="background:#f5f5f5">
            <tr>
                <th class="col-client" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'nom_societe','dir'=>($sort=='nom_societe' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Client {{ $sort=='nom_societe' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-duree" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'duree_mois','dir'=>($sort=='duree_mois' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Durée {{ $sort=='duree_mois' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-heures" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'heures_par_periode','dir'=>($sort=='heures_par_periode' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        H / période {{ $sort=='heures_par_periode' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-echeance" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'date_fin','dir'=>($sort=='date_fin' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Échéance {{ $sort=='date_fin' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-avancement" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'duree_periode_mois','dir'=>($sort=='duree_periode_mois' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Avancement {{ $sort=='duree_periode_mois' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($contrats as $contrat)
            @php
                $expire = $contrat->statut === 'expire';
                $jours = $contrat->jours_restants;
                $couleurEcheance = $expire ? '#aaa' : ($jours <= 30 ? '#dc2626' : ($jours <= 90 ? '#E8720C' : '#166534'));
                $avancementPeriode = $contrat->avancement_periode;
                $couleurBarre = $expire ? '#ccc' : ($avancementPeriode >= 80 ? '#E8720C' : '#166534');
            @endphp
            <tr onclick="window.location='{{ route('contrats.show', $contrat) }}'"
                style="cursor:pointer;border-bottom:1px solid #f0f0f0;{{ $expire ? 'opacity:0.5' : '' }}"
                onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#fff'">
                <td class="col-client" style="padding:10px 14px;font-weight:500">{{ $contrat->client->nom_societe }}</td>
                <td class="col-duree" style="padding:10px 14px;color:#888;font-size:12px">
                    {{ $contrat->duree_mois == 12 ? '1 an' : ($contrat->duree_mois == 24 ? '2 ans' : '3 ans') }}
                </td>
                <td class="col-heures" style="padding:10px 14px;font-weight:500">{{ $contrat->heures_par_periode }}h</td>
                <td class="col-echeance" style="padding:10px 14px;font-size:12px;font-weight:500;color:{{ $couleurEcheance }}">
                    {{ $contrat->echeance_label }}
                </td>
                <td class="col-avancement" style="padding:10px 14px">
                    @if($contrat->statut !== 'non-actif')
                    <div style="display:flex;align-items:center;gap:6px">
                        <div style="flex:1;height:5px;background:#f0f0f0;border-radius:99px;overflow:hidden;min-width:60px">
                            <div style="width:{{ min(100, $avancementPeriode) }}%;height:100%;background:{{ $couleurBarre }};border-radius:99px"></div>
                        </div>
                        <span style="font-size:11px;color:#888">{{ $avancementPeriode }}%</span>
                    </div>
                    @else
                        <span style="font-size:11px;color:#aaa">non démarré</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:2rem;text-align:center;color:#aaa;font-size:13px">Aucun contrat trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:10px 14px;border-top:1px solid #e0e0e0;font-size:12px;color:#888">{{ $contrats->links() }}</div>
</div>

<script>
const PAGE_CONTRATS = 'contrats-colonnes';
const colonnesContrats = [
    { id: 'col-client',     label: 'Client',      visible: true },
    { id: 'col-duree',      label: 'Durée',       visible: true },
    { id: 'col-heures',     label: 'H / période', visible: true },
    { id: 'col-echeance',   label: 'Échéance',    visible: true },
    { id: 'col-avancement', label: 'Avancement',  visible: true },
];

let dragSrcIndex = null;
let dragOverRow = null;

async function chargerPrefsContrats() {
    try {
        const r = await fetch('/preferences/' + encodeURIComponent(PAGE_CONTRATS));
        const data = await r.json();
        if (data && data.preferences) data.preferences.forEach(s => {
            const c = colonnesContrats.find(x => x.id === s.id);
            if (c) c.visible = s.visible;
        });
    } catch(e) {}
    appliquerColonnesContrats();
}

async function sauvegarderPrefsContrats() {
    await fetch('/preferences', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ page: PAGE_CONTRATS, preferences: colonnesContrats })
    });
}

function appliquerColonnesContrats() {
    colonnesContrats.forEach(col => {
        document.querySelectorAll('.' + col.id).forEach(el => {
            el.style.display = col.visible ? '' : 'none';
        });
    });
}

function reorderTableDOMContrats(colonnes) {
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

function appliquerColonnesEtFermerContrats() {
    appliquerColonnesContrats();
    reorderTableDOMContrats(colonnesContrats);
    sauvegarderPrefsContrats();
    document.getElementById('panel-colonnes').style.display = 'none';
}

function renderPanelColonnesContrats() {
    const list = document.getElementById('col-list-contrats');
    list.innerHTML = '';
    colonnesContrats.forEach((col, i) => {
        const row = document.createElement('div');
        row.draggable = true;
        row.dataset.index = i;
        row.style = 'display:flex;align-items:center;gap:8px;padding:6px 4px;border-radius:6px;';
        row.innerHTML = `
            <i class="ti ti-grip-vertical" style="font-size:14px;color:#ccc;cursor:grab" aria-hidden="true"></i>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;font-size:13px;color:#1a1a1a">
                <input type="checkbox" ${col.visible ? 'checked' : ''} onchange="toggleColonneContrat(${i}, this.checked)" style="accent-color:#E8720C;width:15px;height:15px;">
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
                const [moved] = colonnesContrats.splice(dragSrcIndex, 1);
                colonnesContrats.splice(destIndex, 0, moved);
            }
            renderPanelColonnesContrats();
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

function toggleColonneContrat(i, checked) {
    colonnesContrats[i].visible = checked;
    sauvegarderPrefsContrats();
    appliquerColonnesContrats();
}

function resetColonnesContrats() {
    colonnesContrats.forEach(c => c.visible = true);
    renderPanelColonnesContrats();
    sauvegarderPrefsContrats();
    appliquerColonnesContrats();
}

function togglePanelColonnesContrats() {
    const p = document.getElementById('panel-colonnes');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
    if (p.style.display === 'block') renderPanelColonnesContrats();
}

document.addEventListener('click', function(e) {
    const panel = document.getElementById('panel-colonnes');
    const btn = document.getElementById('btn-colonnes');
    if (panel && btn && !panel.contains(e.target) && !btn.contains(e.target)) panel.style.display = 'none';
});

chargerPrefsContrats();
</script>
@endsection
