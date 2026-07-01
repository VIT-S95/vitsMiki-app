@extends('layouts.app')
@section('title', 'Intervention')
@section('content')
<a href="{{ route('interventions.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour aux interventions</a>

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:4px">Intervention — {{ $intervention->client_nom ?? $intervention->contrat?->client?->nom_societe ?? '—' }}</h1>
        <p style="font-size:13px;color:#888">Bon n° {{ $intervention->numero_bon_kizeo ?? $intervention->id }} — {{ $intervention->date_intervention->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('interventions.edit', $intervention) }}" style="padding:8px 16px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border-radius:8px;text-decoration:none">✏ Modifier</a>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem;margin-bottom:1rem">
    {{-- IDENTIFICATION --}}
    <div style="margin-bottom:1.5rem">
        <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Date</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->date_intervention->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Heure</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->heure_intervention ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Technicien</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->technicien ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Client</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->client_nom ?? $intervention->contrat?->client?->nom_societe ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Contrat</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">
                    @if($intervention->contrat)
                        <a href="{{ route('contrats.show', $intervention->contrat_id) }}" style="color:#E8720C;text-decoration:none">{{ $intervention->contrat->numero_contrat_vits ?? 'Voir le contrat' }}</a>
                    @else
                        <span style="color:#aaa">Hors contrat</span>
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">N° bon Kizeo</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px;font-family:monospace">{{ $intervention->numero_bon_kizeo ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- TYPE & DURÉE --}}
    <div style="margin-bottom:1.5rem">
        <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Type &amp; durée</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Type</div>
                <div style="margin-top:4px">
                    @if($intervention->type === 'site')
                        <span style="background:#E6F1FB;color:#0C447C;padding:2px 8px;border-radius:4px;font-size:11px">sur site</span>
                    @elseif($intervention->type === 'distance')
                        <span style="background:#E1F5EE;color:#085041;padding:2px 8px;border-radius:4px;font-size:11px">à distance</span>
                    @elseif($intervention->type === 'administrateur')
                        <span style="background:#F3F0FF;color:#4C1D95;padding:2px 8px;border-radius:4px;font-size:11px">admin</span>
                    @elseif($intervention->type === 'ajustement')
                        <span style="background:#F0F0F0;color:#555;padding:2px 8px;border-radius:4px;font-size:11px">{{ $intervention->duree_minutes < 0 ? 'Report n-1' : 'Dépassement n-1' }}</span>
                    @else
                        <span style="background:#FFF3E6;color:#854F0B;padding:2px 8px;border-radius:4px;font-size:11px">flash {{ $intervention->flash_numero }}/3</span>
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Durée</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->duree_formatee }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Statut</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->statut ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Déductible</div>
                <div style="margin-top:4px">
                    @if($intervention->deductible)
                        <span style="background:#f0fdf4;color:#166534;padding:2px 8px;border-radius:4px;font-size:11px">Déductible</span>
                    @else
                        <span style="background:#f0f0f0;color:#777;padding:2px 8px;border-radius:4px;font-size:11px">Non déductible</span>
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Hors heure ouvrée</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->hors_heure_ouvree ? 'Oui' : 'Non' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Origine</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->source_manuelle ? 'Saisie manuelle' : ($intervention->source_kizeo ? 'Kizeo' : '—') }}</div>
            </div>
        </div>
    </div>

    {{-- DEMANDE --}}
    <div style="margin-bottom:1.5rem">
        <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Demande</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Donneur d'ordre</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->donneur_ordre ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">N° ticket</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->n_ticket ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">N° devis</div>
                <div style="font-size:13px;font-weight:500;margin-top:2px">{{ $intervention->n_devis ?? '—' }}</div>
            </div>
            <div style="grid-column:1/-1">
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Demande annexe</div>
                <div style="font-size:13px;margin-top:2px;white-space:pre-wrap">{{ $intervention->demande_annexe ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- PIÈCES & COMMENTAIRES --}}
    <div>
        <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Pièces &amp; commentaires</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Pièces détachées</div>
                <div style="font-size:13px;margin-top:2px;white-space:pre-wrap">{{ $intervention->pieces_detachees ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#aaa;text-transform:uppercase">Commentaires</div>
                <div style="font-size:13px;margin-top:2px;white-space:pre-wrap">{{ $intervention->commentaires ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

@if($intervention->raw_data)
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1rem 1.5rem">
    <button type="button" onclick="var el=document.getElementById('raw-data');el.style.display=el.style.display==='none'?'block':'none';this.textContent=el.style.display==='none'?'Voir données brutes':'Masquer les données brutes'"
        style="font-size:12px;color:#888;background:#f5f5f5;border:1px solid #ddd;padding:6px 12px;border-radius:8px;cursor:pointer">Voir données brutes</button>
    <pre id="raw-data" style="display:none;margin-top:1rem;font-size:11px;color:#555;background:#fafafa;border:1px solid #f0f0f0;border-radius:8px;padding:1rem;overflow:auto;max-height:400px">{{ json_encode($intervention->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endif
@endsection
