@extends('layouts.app')
@section('title', 'Générer un PDF')
@section('content')
<a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour au contrat</a>

<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:4px">Générer un rapport PDF</h1>
<p style="font-size:13px;color:#888;margin-bottom:1.5rem">{{ $contrat->client->nom_societe }} — {{ $contrat->numero_contrat_vits }}</p>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <div style="font-size:13px;font-weight:500;color:#1a1a1a;margin-bottom:1rem">Choisir la période à inclure</div>

    {{-- Option toutes les périodes --}}
    @php $tooLarge = $totalInterventions > 300; @endphp
    <div style="margin-bottom:1rem;padding:12px 14px;border:1px solid {{ $tooLarge ? '#fca5a5' : '#e0e0e0' }};border-radius:8px;background:{{ $tooLarge ? '#fef2f2' : '#f9f9f9' }}">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:500">Toutes les périodes</div>
                <div style="font-size:12px;color:#888;margin-top:2px">{{ $totalInterventions }} interventions au total</div>
                @if($tooLarge)
                    <div style="font-size:11px;color:#dc2626;margin-top:4px">⚠ Volume important — la génération peut être lente</div>
                @endif
            </div>
            <div style="display:flex;gap:6px">
                <a href="{{ route('contrats.pdf', $contrat) }}?periode=toutes&action=stream" target="_blank"
                   style="padding:6px 10px;font-size:12px;background:{{ $tooLarge ? '#dc2626' : '#E8720C' }};color:#fff;border-radius:6px;text-decoration:none">
                    🖨 Imprimer
                </a>
                <a href="{{ route('contrats.pdf', $contrat) }}?periode=toutes&action=download" target="_blank"
                   style="padding:6px 10px;font-size:12px;background:#f5f5f5;color:#444;border:1px solid #ddd;border-radius:6px;text-decoration:none">
                    ⬇ Télécharger
                </a>
            </div>
        </div>
    </div>

    {{-- Une période à la fois --}}
    <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;margin-bottom:8px;margin-top:1.5rem">Ou choisir une période spécifique</div>
    @foreach($periodes as $periode)
    @php
        $enCours = now()->between($periode['debut'], $periode['fin']);
        $nb = $periode['nb_interventions'];
        $gros = $nb > 300;
    @endphp
    <div style="margin-bottom:8px;padding:12px 14px;border:1px solid {{ $enCours ? '#E8720C' : '#e0e0e0' }};border-radius:8px;background:{{ $enCours ? '#FFF9F5' : '#fff' }}">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:13px;font-weight:500;color:{{ $enCours ? '#E8720C' : '#1a1a1a' }}">
                    Période {{ $periode['numero'] }} — {{ $periode['debut']->format('d/m/Y') }} → {{ $periode['fin']->format('d/m/Y') }}
                    @if($enCours)<span style="font-size:11px;background:#E8720C;color:#fff;padding:1px 6px;border-radius:99px;margin-left:6px">En cours</span>@endif
                </div>
                <div style="font-size:12px;color:#888;margin-top:2px">
                    {{ $nb }} intervention{{ $nb > 1 ? 's' : '' }}
                    @if($gros)<span style="color:#dc2626"> — ⚠ volume important</span>@endif
                </div>
            </div>
            <div style="display:flex;gap:6px">
                <a href="{{ route('contrats.pdf', $contrat) }}?periode={{ $periode['numero'] }}&action=stream" target="_blank"
                   style="padding:6px 10px;font-size:12px;background:#E8720C;color:#fff;border-radius:6px;text-decoration:none">
                    🖨 Imprimer
                </a>
                <a href="{{ route('contrats.pdf', $contrat) }}?periode={{ $periode['numero'] }}&action=download" target="_blank"
                   style="padding:6px 10px;font-size:12px;background:#f5f5f5;color:#444;border:1px solid #ddd;border-radius:6px;text-decoration:none">
                    ⬇ Télécharger
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
