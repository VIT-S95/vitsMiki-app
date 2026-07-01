@extends('layouts.app')
@section('title', 'Clients')
@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Clients</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $clients->total() }} clients</p>
    </div>
    <a href="{{ route('clients.create') }}" style="padding:7px 14px;background:#E8720C;color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none">+ Nouveau client</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

<form method="GET" style="display:flex;gap:8px;margin-bottom:1rem">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client…" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;width:300px">
    <button type="submit" style="padding:7px 12px;background:#f5f5f5;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">Rechercher</button>
    <select name="per_page" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:110px">
        <option value="20" {{ request('per_page',20)==20?'selected':'' }}>20 / page</option>
        <option value="50" {{ request('per_page',20)==50?'selected':'' }}>50 / page</option>
        <option value="100" {{ request('per_page',20)==100?'selected':'' }}>100 / page</option>
    </select>
    @if(request('search'))<a href="{{ route('clients.index') }}" style="font-size:13px;color:#888;padding:7px 0">Effacer</a>@endif
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#666;cursor:pointer;padding:7px 0">
        <input type="checkbox" name="sans_contrat" value="1" {{ request('sans_contrat')=='1'?'checked':'' }} onchange="this.form.submit()" style="accent-color:#E8720C">
        Afficher clients sans contrat
    </label>
    <div style="position:relative;display:inline-block;margin-left:auto">
        <button type="button" id="btn-colonnes" onclick="togglePanelColonnesClients()" style="padding:7px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px;background:#fff;cursor:pointer;display:flex;align-items:center;gap:6px;color:#444">
            <i class="ti ti-columns" style="font-size:15px" aria-hidden="true"></i> Colonnes
        </button>
        <div id="panel-colonnes" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:12px;width:240px;z-index:100;box-shadow:0 4px 16px rgba(0,0,0,0.1)">
            <div style="font-size:11px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px">Affichage des colonnes</div>
            <div id="col-list-clients"></div>
            <div style="border-top:1px solid #f0f0f0;margin-top:10px;padding-top:10px;display:flex;justify-content:space-between">
                <button type="button" onclick="resetColonnesClients()" style="font-size:12px;color:#888;background:none;border:none;cursor:pointer">Réinitialiser</button>
                <button type="button" onclick="appliquerColonnesEtFermerClients()" style="font-size:12px;background:#E8720C;color:#fff;border:none;border-radius:6px;padding:5px 12px;cursor:pointer">Appliquer</button>
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
                <th class="col-kizeo" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'numero_client_kizeo','dir'=>($sort=='numero_client_kizeo' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        N° Kizeo {{ $sort=='numero_client_kizeo' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-contrat" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'numero_contrat_vits','dir'=>($sort=='numero_contrat_vits' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        N° Contrat VIT-S {{ $sort=='numero_contrat_vits' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th class="col-statut" style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'statut','dir'=>($sort=='statut' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Statut {{ $sort=='statut' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr onclick="window.location='{{ route('clients.show', $client) }}'" style="cursor:pointer;border-bottom:1px solid #f0f0f0" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#fff'">
                <td class="col-client" style="padding:10px 14px;font-weight:500">{{ $client->nom_societe }}</td>
                <td class="col-kizeo" style="padding:10px 14px;color:#888;font-family:monospace;font-size:12px">{{ $client->numero_client_kizeo ?? '—' }}</td>
                <td class="col-contrat" style="padding:10px 14px;color:#888;font-family:monospace;font-size:12px">{{ $client->contrats->first()->numero_contrat_vits ?? '—' }}</td>
                <td class="col-statut" style="padding:10px 14px">
                    @if($client->statut === 'actif')
                        <span style="background:#f0fdf4;color:#166534;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">✓ Sous contrat</span>
                    @elseif($client->statut === 'sans_contrat')
                        <span style="background:#fffbeb;color:#92400e;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">Sans contrat</span>
                    @else
                        <span style="background:#fef2f2;color:#dc2626;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">Inactif</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="padding:2rem;text-align:center;color:#aaa;font-size:13px">Aucun client trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:10px 14px;border-top:1px solid #e0e0e0;font-size:12px;color:#888">{{ $clients->links() }}</div>
</div>

<script>
const PAGE_CLIENTS = 'clients-colonnes';
const colonnesClients = [
    { id: 'col-client',  label: 'Client',           visible: true },
    { id: 'col-kizeo',   label: 'N° Kizeo',         visible: true },
    { id: 'col-contrat', label: 'N° Contrat VIT-S', visible: true },
    { id: 'col-statut',  label: 'Statut',           visible: true },
];

let dragSrcIndex = null;
let dragOverRow = null;

async function chargerPrefsClients() {
    try {
        const r = await fetch('/preferences/' + encodeURIComponent(PAGE_CLIENTS));
        const data = await r.json();
        if (data && data.preferences) data.preferences.forEach(s => {
            const c = colonnesClients.find(x => x.id === s.id);
            if (c) c.visible = s.visible;
        });
    } catch(e) {}
    appliquerColonnesClients();
}

async function sauvegarderPrefsClients() {
    await fetch('/preferences', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ page: PAGE_CLIENTS, preferences: colonnesClients })
    });
}

function appliquerColonnesClients() {
    colonnesClients.forEach(col => {
        document.querySelectorAll('.' + col.id).forEach(el => {
            el.style.display = col.visible ? '' : 'none';
        });
    });
}

function reorderTableDOMClients(colonnes) {
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

function appliquerColonnesEtFermerClients() {
    appliquerColonnesClients();
    reorderTableDOMClients(colonnesClients);
    sauvegarderPrefsClients();
    document.getElementById('panel-colonnes').style.display = 'none';
}

function renderPanelColonnesClients() {
    const list = document.getElementById('col-list-clients');
    list.innerHTML = '';
    colonnesClients.forEach((col, i) => {
        const row = document.createElement('div');
        row.draggable = true;
        row.dataset.index = i;
        row.style = 'display:flex;align-items:center;gap:8px;padding:6px 4px;border-radius:6px;';
        row.innerHTML = `
            <i class="ti ti-grip-vertical" style="font-size:14px;color:#ccc;cursor:grab" aria-hidden="true"></i>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;flex:1;font-size:13px;color:#1a1a1a">
                <input type="checkbox" ${col.visible ? 'checked' : ''} onchange="toggleColonneClient(${i}, this.checked)" style="accent-color:#E8720C;width:15px;height:15px;">
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
                const [moved] = colonnesClients.splice(dragSrcIndex, 1);
                colonnesClients.splice(destIndex, 0, moved);
            }
            renderPanelColonnesClients();
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

function toggleColonneClient(i, checked) {
    colonnesClients[i].visible = checked;
    sauvegarderPrefsClients();
    appliquerColonnesClients();
}

function resetColonnesClients() {
    colonnesClients.forEach(c => c.visible = true);
    renderPanelColonnesClients();
    sauvegarderPrefsClients();
    appliquerColonnesClients();
}

function togglePanelColonnesClients() {
    const p = document.getElementById('panel-colonnes');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
    if (p.style.display === 'block') renderPanelColonnesClients();
}

document.addEventListener('click', function(e) {
    const panel = document.getElementById('panel-colonnes');
    const btn = document.getElementById('btn-colonnes');
    if (panel && btn && !panel.contains(e.target) && !btn.contains(e.target)) panel.style.display = 'none';
});

chargerPrefsClients();
</script>
@endsection
