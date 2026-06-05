@extends('layouts.portail')
@section('title', 'Tableau de bord')
@section('content')

<div style="margin-bottom:1.5rem">
    <div style="font-size:22px;font-weight:600;color:#1a1a1a">Bienvenue, {{ $client->nom_societe }}</div>
    <div style="font-size:13px;color:#888;margin-top:4px">Accès portail client VIT-S</div>
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:3rem;text-align:center">
    <div style="font-size:40px;margin-bottom:1rem">🔧</div>
    <div style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:0.5rem">Portail en construction</div>
    <div style="font-size:14px;color:#888;max-width:400px;margin:0 auto">
        Votre espace client est en cours de développement.<br>
        Les fonctionnalités seront bientôt disponibles.
    </div>
</div>

@endsection
