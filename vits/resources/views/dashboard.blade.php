@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem">
        <div>
            <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Tableau de bord</h1>
            <p style="font-size:13px;color:#888;margin-top:2px">{{ now()->isoFormat('dddd D MMMM YYYY') }} — Vue d'ensemble des alertes actives</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:1.5rem">
        <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
            <div style="font-size:12px;color:#888;margin-bottom:6px">Clients actifs</div>
            <div style="font-size:22px;font-weight:500;color:#1a1a1a">0</div>
        </div>
        <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
            <div style="font-size:12px;color:#888;margin-bottom:6px">Contrats en cours</div>
            <div style="font-size:22px;font-weight:500;color:#E8720C">0</div>
        </div>
        <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
            <div style="font-size:12px;color:#888;margin-bottom:6px">Interventions ce mois</div>
            <div style="font-size:22px;font-weight:500;color:#1a1a1a">0</div>
        </div>
        <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
            <div style="font-size:12px;color:#888;margin-bottom:6px">Alertes actives</div>
            <div style="font-size:22px;font-weight:500;color:#dc2626">0</div>
        </div>
    </div>

    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:2rem;text-align:center;color:#888">
        <i class="ti ti-layout-dashboard" style="font-size:48px;color:#e0e0e0"></i>
        <p style="margin-top:1rem;font-size:14px">Le tableau de bord se remplira au fur et à mesure qu'on ajoute les clients et contrats.</p>
    </div>
@endsection