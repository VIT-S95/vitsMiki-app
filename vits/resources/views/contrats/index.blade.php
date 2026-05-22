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
    <select name="statut" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
        <option value="">Tous les statuts</option>
        <option value="en-cours" {{ request('statut')=='en-cours'?'selected':'' }}>En cours</option>
        <option value="expire" {{ request('statut')=='expire'?'selected':'' }}>Expirés</option>
        <option value="non-actif" {{ request('statut')=='non-actif'?'selected':'' }}>Non actifs</option>
    </select>
    <button type="submit" style="padding:7px 12px;background:#f5f5f5;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">Filtrer</button>
    @if(request('search') || request('statut'))<a href="{{ route('contrats.index') }}" style="font-size:13px;color:#888;padding:7px 0">Effacer</a>@endif
</form>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead style="background:#f5f5f5">
            <tr>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'nom_societe','dir'=>($sort=='nom_societe' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Client {{ $sort=='nom_societe' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'duree_mois','dir'=>($sort=='duree_mois' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Durée {{ $sort=='duree_mois' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'heures_par_periode','dir'=>($sort=='heures_par_periode' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        H / période {{ $sort=='heures_par_periode' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'date_fin','dir'=>($sort=='date_fin' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Échéance {{ $sort=='date_fin' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'duree_periode_mois','dir'=>($sort=='duree_periode_mois' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Avancement {{ $sort=='duree_periode_mois' ? ($dir=='asc'?'↑':'↓') : '' }}
                    </a>
                </th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">
                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'statut','dir'=>($sort=='statut' && $dir=='asc')?'desc':'asc']) }}" style="color:#888;text-decoration:none">
                        Statut {{ $sort=='statut' ? ($dir=='asc'?'↑':'↓') : '' }}
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
                $couleurBarre = $expire ? '#ccc' : ($contrat->avancement >= 80 ? '#E8720C' : '#166534');
            @endphp
            <tr onclick="window.location='{{ route('contrats.show', $contrat) }}'"
                style="cursor:pointer;border-bottom:1px solid #f0f0f0;{{ $expire ? 'opacity:0.5' : '' }}"
                onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='#fff'">
                <td style="padding:10px 14px;font-weight:500">{{ $contrat->client->nom_societe }}</td>
                <td style="padding:10px 14px;color:#888;font-size:12px">
                    {{ $contrat->duree_mois == 12 ? '1 an' : ($contrat->duree_mois == 24 ? '2 ans' : '3 ans') }}
                </td>
                <td style="padding:10px 14px;font-weight:500">{{ $contrat->heures_par_periode }}h</td>
                <td style="padding:10px 14px;font-size:12px;font-weight:500;color:{{ $couleurEcheance }}">
                    {{ $contrat->echeance_label }}
                </td>
                <td style="padding:10px 14px">
                    @if($contrat->statut !== 'non-actif')
                    <div style="display:flex;align-items:center;gap:6px">
                        <div style="flex:1;height:5px;background:#f0f0f0;border-radius:99px;overflow:hidden;min-width:60px">
                            <div style="width:{{ $contrat->avancement }}%;height:100%;background:{{ $couleurBarre }};border-radius:99px"></div>
                        </div>
                        <span style="font-size:11px;color:#888">{{ $contrat->avancement }}%</span>
                    </div>
                    @else
                        <span style="font-size:11px;color:#aaa">non démarré</span>
                    @endif
                </td>
                <td style="padding:10px 14px">
                    @if($contrat->statut === 'en-cours')
                        <span style="background:#FFF3E6;color:#E8720C;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">En cours</span>
                    @elseif($contrat->statut === 'expire')
                        <span style="background:#f5f5f5;color:#aaa;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">Expiré</span>
                    @else
                        <span style="background:#f5f5f5;color:#888;padding:3px 8px;border-radius:99px;font-size:11px;font-weight:500">Non actif</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="padding:2rem;text-align:center;color:#aaa;font-size:13px">Aucun contrat trouvé</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:10px 14px;border-top:1px solid #e0e0e0;font-size:12px;color:#888">{{ $contrats->links() }}</div>
</div>
@endsection
