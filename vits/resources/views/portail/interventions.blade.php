@extends('layouts.portail')
@section('title', 'Interventions')
@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <div style="font-size:18px;font-weight:600;color:#1a1a1a">Interventions</div>
        <div style="font-size:13px;color:#888;margin-top:2px">Historique complet · {{ $interventions->total() }} au total</div>
    </div>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    @if($interventions->isEmpty())
        <div style="padding:3rem;text-align:center;color:#aaa;font-size:13px">Aucune intervention enregistrée.</div>
    @else
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#fafafa;border-bottom:1px solid #e0e0e0">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:500">Date</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:500">Technicien</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:500">Type</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:500">Motif</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;color:#aaa;text-transform:uppercase;font-weight:500">Durée</th>
                </tr>
            </thead>
            <tbody>
                @foreach($interventions as $i)
                @php
                    $hI = intdiv(abs($i->duree_minutes), 60);
                    $mI = abs($i->duree_minutes) % 60;
                    $dureeStr = $i->type === 'flash' && $i->flash_numero < 3
                        ? '—'
                        : (($hI > 0 ? $hI.'h ' : '').($mI > 0 ? $mI.'min' : ($hI === 0 ? '0min' : '')));
                    $nonDed = !$i->deductible;
                @endphp
                <tr style="border-top:1px solid #f5f5f5;{{ $nonDed ? 'opacity:0.5' : '' }}">
                    <td style="padding:10px 16px;color:#888;white-space:nowrap">{{ $i->date_intervention->format('d/m/Y') }}</td>
                    <td style="padding:10px 16px;color:#555">{{ $i->technicien ?? '—' }}</td>
                    <td style="padding:10px 16px">
                        @if($i->type === 'site')
                            <span style="background:#E6F1FB;color:#0C447C;padding:2px 7px;border-radius:4px;font-size:11px">Sur site</span>
                        @elseif($i->type === 'distance')
                            <span style="background:#E1F5EE;color:#085041;padding:2px 7px;border-radius:4px;font-size:11px">À distance</span>
                        @elseif($i->type === 'administrateur')
                            <span style="background:#F3F0FF;color:#4C1D95;padding:2px 7px;border-radius:4px;font-size:11px">Admin</span>
                        @else
                            <span style="background:#FFF3E6;color:#854F0B;padding:2px 7px;border-radius:4px;font-size:11px">Flash {{ $i->flash_numero }}/3</span>
                        @endif
                        @if($i->hors_contrat)
                            <span style="background:#f5f5f5;color:#888;padding:2px 6px;border-radius:4px;font-size:10px;margin-left:4px">Hors contrat</span>
                        @endif
                        @if($nonDed)
                            <span style="background:#f5f5f5;color:#aaa;padding:2px 6px;border-radius:4px;font-size:10px;margin-left:4px">Non déductible</span>
                        @endif
                    </td>
                    <td style="padding:10px 16px;color:#888;font-size:12px">{{ $i->motif ?? '' }}</td>
                    <td style="padding:10px 16px;text-align:right;font-weight:500;color:{{ $i->duree_minutes < 0 ? '#166534' : '#1a1a1a' }}">
                        {{ $i->duree_minutes < 0 ? '+' : '' }}{{ $dureeStr }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($interventions->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #f0f0f0;display:flex;justify-content:center">
            {{ $interventions->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
