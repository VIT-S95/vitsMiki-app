@extends('layouts.app')
@section('title', 'Corbeille')
@section('content')

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Corbeille — Interventions supprimées</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $interventions->total() }} intervention(s) dans la corbeille</p>
    </div>
</div>

@if($interventions->count())
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead style="background:#f5f5f5">
            <tr>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Date suppression</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Supprimé par</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Date intervention</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Client</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Technicien</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Type</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Durée</th>
                <th style="padding:9px 14px;text-align:right;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($interventions as $intervention)
            <tr style="border-bottom:1px solid #f0f0f0">
                <td style="padding:10px 14px">
                    <span style="display:inline-block;background:#fef2f2;color:#dc2626;font-size:11px;font-weight:600;padding:3px 8px;border-radius:6px">
                        Supprimé le {{ $intervention->deleted_at?->format('d/m/Y') }} par {{ $intervention->deletedBy?->name ?? 'Inconnu' }}
                    </span>
                </td>
                <td style="padding:10px 14px;color:#555">{{ $intervention->deletedBy?->name ?? '—' }}</td>
                <td style="padding:10px 14px;color:#888">{{ $intervention->date_intervention?->format('d/m/Y') }}{{ $intervention->heure_intervention ? ' '.$intervention->heure_intervention : '' }}</td>
                <td style="padding:10px 14px;font-weight:500">{{ $intervention->client_nom ?? $intervention->contrat?->client?->nom_societe ?? '—' }}</td>
                <td style="padding:10px 14px;color:#555;font-size:12px">{{ $intervention->technicien ?? '—' }}</td>
                <td style="padding:10px 14px;color:#555">{{ ucfirst($intervention->type) }}</td>
                <td style="padding:10px 14px;text-align:right;font-weight:500">{{ $intervention->duree_formatee }}</td>
                <td style="padding:10px 14px;text-align:right;white-space:nowrap">
                    <form action="{{ route('corbeille.restaurer', $intervention->id) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('Restaurer cette intervention ?')">
                        @csrf
                        <button type="submit" style="padding:5px 10px;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer">
                            <i class="ti ti-restore"></i> Restaurer
                        </button>
                    </form>
                    <form action="{{ route('corbeille.force-delete', $intervention->id) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('Supprimer DÉFINITIVEMENT cette intervention ? Cette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:5px 10px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer">
                            <i class="ti ti-trash"></i> Supprimer définitivement
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:1rem">
    {{ $interventions->links() }}
</div>
@else
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:3rem;text-align:center;color:#aaa;font-size:14px">
    <div style="font-size:32px;margin-bottom:0.5rem">🗑️</div>
    Corbeille vide
</div>
@endif

@if(session('success'))
<div id="success-modal" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:9999">
    <div style="background:#fff;border-radius:12px;padding:2rem 2.5rem;max-width:480px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center">
        <div style="font-size:24px;margin-bottom:0.75rem">✅</div>
        <div style="font-size:14px;color:#1a1a1a;line-height:1.6;margin-bottom:1.5rem">{{ session('success') }}</div>
        <button onclick="document.getElementById('success-modal').remove()" style="padding:8px 24px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer">OK</button>
    </div>
</div>
@endif

@endsection
