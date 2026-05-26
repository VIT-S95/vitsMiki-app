@extends('layouts.app')
@section('title', 'Clients')
@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Clients</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $clients->total() }} clients — triés par ordre alphabétique</p>
    </div>
    <a href="{{ route('clients.create') }}" style="padding:7px 14px;background:#E8720C;color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none">+ Nouveau client</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

<form method="GET" style="display:flex;gap:8px;margin-bottom:1rem">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client…" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;width:300px">
    <button type="submit" style="padding:7px 12px;background:#f5f5f5;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">Rechercher</button>
    <select name="per_page" onchange="this.form.submit()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        <option value="20" {{ request('per_page',20)==20?'selected':'' }}>20 / page</option>
        <option value="50" {{ request('per_page',20)==50?'selected':'' }}>50 / page</option>
        <option value="100" {{ request('per_page',20)==100?'selected':'' }}>100 / page</option>
    </select>
    @if(request('search'))<a href="{{ route('clients.index') }}" style="font-size:13px;color:#888;padding:7px 0">Effacer</a>@endif
    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#666;cursor:pointer;padding:7px 0">
        <input type="checkbox" name="sans_contrat" value="1" {{ request('sans_contrat')=='1'?'checked':'' }} onchange="this.form.submit()" style="accent-color:#E8720C">
        Afficher sans contrat
    </label>
</form>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead style="background:#f5f5f5">
            <tr>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Client</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">N° Kizeo</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">N° Contrat VIT-S</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Statut</th>
                <th style="padding:9px 14px;border-bottom:1px solid #e0e0e0"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr onclick="window.location='{{ route('clients.show', $client) }}'" style="cursor:pointer;border-bottom:1px solid #f0f0f0" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#fff'">
                <td style="padding:10px 14px;font-weight:500">{{ $client->nom_societe }}</td>
                <td style="padding:10px 14px;color:#888;font-family:monospace;font-size:12px">{{ $client->numero_client_kizeo ?? '—' }}</td>
                <td style="padding:10px 14px;color:#888;font-family:monospace;font-size:12px">{{ $client->contrats->first()->numero_contrat_vits ?? '—' }}</td>
                <td style="padding:10px 14px">
                    @if($client->statut === 'actif')
                        <span style="background:#f0fdf4;color:#166534;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">✓ Actif</span>
                    @else
                        <span style="background:#fef2f2;color:#dc2626;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">Inactif</span>
                    @endif
                </td>
                <td style="padding:10px 14px;text-align:right">
                    <a href="{{ route('clients.edit', $client) }}" onclick="event.stopPropagation()" style="font-size:12px;color:#888;text-decoration:none;padding:4px 8px;border:1px solid #ddd;border-radius:6px">Modifier</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="padding:2rem;text-align:center;color:#aaa;font-size:13px">Aucun client trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:10px 14px;border-top:1px solid #e0e0e0;font-size:12px;color:#888">{{ $clients->links() }}</div>
</div>
@endsection
