@extends('layouts.app')
@section('title', 'Fusion des saisies manuelles')
@section('content')
<div style="margin-bottom:1.5rem">
    <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Fusion des saisies manuelles</h1>
    <p style="font-size:13px;color:#888;margin-top:2px">Rattacher les noms saisis librement vers les vrais clients Kizeo</p>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

@if($saisiesManuelles->isEmpty())
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:3rem;text-align:center;color:#aaa;font-size:13px">
    Aucune saisie manuelle à fusionner.
</div>
@else
<div style="display:flex;flex-direction:column;gap:8px">
    @foreach($saisiesManuelles as $saisie)
    @php
        $h = intdiv($saisie->total_minutes, 60);
        $m = $saisie->total_minutes % 60;
        $duree = ($h > 0 ? $h.'h' : '').($h > 0 && $m > 0 ? ' ' : '').($m > 0 ? $m.'min' : ($h === 0 ? '0min' : ''));
    @endphp
    <div class="fusion-card" id="card-{{ $loop->index }}" style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1rem 1.25rem">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <div style="flex:1;min-width:180px">
                <div style="font-size:13px;font-weight:500;color:#1a1a1a">{{ $saisie->client_nom }}</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">
                    {{ $saisie->nb_interventions }} intervention{{ $saisie->nb_interventions > 1 ? 's' : '' }}
                    &nbsp;·&nbsp; {{ $duree }}
                    &nbsp;·&nbsp; <span style="font-size:11px;color:#b84c00;background:#fff0e8;border:1px solid #f5b48a;padding:1px 5px;border-radius:3px">Saisie manuelle</span>
                </div>
            </div>
            <form method="POST" action="{{ route('clients.fusion.store') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                @csrf
                <input type="hidden" name="client_nom_source" value="{{ $saisie->client_nom }}">
                <select name="client_id_cible" required
                        style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;min-width:220px;max-width:320px">
                    <option value="">— Fusionner avec... —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->nom_societe }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        onclick="return confirm('Fusionner toutes les interventions de « {{ addslashes($saisie->client_nom) }} » vers ce client ?')"
                        style="padding:7px 16px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer;white-space:nowrap">
                    Fusionner
                </button>
            </form>
            <button type="button"
                    onclick="document.getElementById('card-{{ $loop->index }}').style.display='none'"
                    style="padding:7px 14px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#888;background:#fff;cursor:pointer;white-space:nowrap">
                Ignorer
            </button>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
