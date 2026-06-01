@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Tableau de bord</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ now()->isoFormat('dddd D MMMM YYYY') }} — Vue d'ensemble des alertes actives</p>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">✗ {{ session('error') }}</div>
@endif

{{-- MÉTRIQUES --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:1rem">
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Clients actifs</div>
        <div style="font-size:22px;font-weight:500;color:#1a1a1a">{{ $totalClients }}</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Contrats en cours</div>
        <div style="font-size:22px;font-weight:500;color:#E8720C">{{ $totalContrats }}</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Interventions ce mois</div>
        <div style="font-size:22px;font-weight:500;color:#1a1a1a">{{ $interventionsMois }}</div>
    </div>
    <div style="background:#f5f5f5;border-radius:8px;padding:0.875rem 1rem">
        <div style="font-size:12px;color:#888;margin-bottom:6px">Alertes actives</div>
        <div style="font-size:22px;font-weight:500;color:#dc2626">{{ $totalAlertes }}</div>
    </div>
</div>

{{-- KIZEO --}}
<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:1rem;overflow:hidden">
    {{-- Barre principale --}}
    <div style="padding:10px 16px;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
            <form method="POST" action="{{ route('kizeo.forcer') }}" id="form-kizeo">
                @csrf
                <button type="submit" id="btn-kizeo" title="Synchroniser maintenant"
                    style="width:32px;height:32px;border-radius:8px;background:#FFF3E6;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;padding:0;transition:background 0.2s"
                    onmouseover="this.style.background='#FFE0C2'" onmouseout="this.style.background='#FFF3E6'"
                    onclick="this.innerHTML='⏳';this.disabled=true;localStorage.setItem('kizeo_syncing','1')">
                    ☁
                </button>
            </form>
            <div>
                <div style="font-size:13px;font-weight:500">Synchronisation Kizeo
                    <span style="font-size:11px;font-weight:400;color:#aaa;margin-left:6px">— prochaine dans <span id="kizeo-countdown" style="color:#E8720C;font-weight:600">…</span></span>
                </div>
                <div style="font-size:12px;color:#888;margin-top:1px">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $derniereImport ? '#166534' : '#aaa' }};margin-right:4px;vertical-align:middle"></span>
                    Dernière import :
                    <strong>{{ $derniereImport ? $derniereImport->locale('fr')->diffForHumans() : 'jamais' }}</strong>
                    @if($derniereImport)
                        <span style="color:#bbb">({{ $derniereImport->format('d/m/Y H:i') }})</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:16px">
            <div style="text-align:center"><div style="font-size:16px;font-weight:500">{{ $nonLus['site'] }}</div><div style="font-size:11px;color:#aaa">sur site</div></div>
            <div style="width:1px;height:32px;background:#e0e0e0"></div>
            <div style="text-align:center"><div style="font-size:16px;font-weight:500">{{ $nonLus['distance'] }}</div><div style="font-size:11px;color:#aaa">à distance</div></div>
            <div style="width:1px;height:32px;background:#e0e0e0"></div>
            <button onclick="document.getElementById('kizeo-log').style.display=document.getElementById('kizeo-log').style.display==='none'?'block':'none'"
                style="background:none;border:none;cursor:pointer;font-size:11px;color:#aaa;padding:4px 8px;border-radius:6px;border:1px solid #eee">
                Détails ▾
            </button>
        </div>
    </div>

    {{-- Panneau de contrôle --}}
    <div id="kizeo-log" style="display:none;border-top:1px solid #f0f0f0;padding:12px 16px;background:#fafafa">
        @if($kizeoLog)
        @php
            $icones = ['ok' => '✅', 'partiel' => '⚠️', 'erreur' => '❌'];
            $icone  = $icones[$kizeoLog['statut']] ?? '❓';
        @endphp
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;font-size:12px">
            <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:#fff;border-radius:8px;border:1px solid #eee">
                <span>{{ $kizeoLog['api_ok'] ? '✅' : '❌' }}</span>
                <div>
                    <div style="font-weight:500;color:#555">Clé API</div>
                    <div style="color:#aaa">{{ $kizeoLog['api_ok'] ? 'Configurée' : 'Non configurée' }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:#fff;border-radius:8px;border:1px solid #eee">
                <span>🕐</span>
                <div>
                    <div style="font-weight:500;color:#555">Début import</div>
                    <div style="color:#aaa">{{ $kizeoLog['debut'] }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:#fff;border-radius:8px;border:1px solid #eee">
                <span>🕐</span>
                <div>
                    <div style="font-weight:500;color:#555">Fin import</div>
                    <div style="color:#aaa">{{ $kizeoLog['fin'] }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:#fff;border-radius:8px;border:1px solid #eee">
                <span>{{ $icone }}</span>
                <div>
                    <div style="font-weight:500;color:#555">Résultat</div>
                    <div style="color:#aaa">{{ $kizeoLog['imported'] }} importée(s)@if($kizeoLog['errors'] > 0), {{ $kizeoLog['errors'] }} erreur(s)@endif</div>
                </div>
            </div>
        </div>
        @if(!empty($kizeoLog['details']))
        <div style="margin-top:8px;font-size:11px;color:#aaa">
            @foreach($kizeoLog['details'] as $detail)
            <div>⚠ {{ $detail }}</div>
            @endforeach
        </div>
        @endif
        @else
        <div style="font-size:12px;color:#bbb;text-align:center;padding:8px">Aucun import effectué depuis le démarrage du serveur.</div>
        @endif
    </div>
</div>

<script>
(function() {
    const STORAGE_KEY  = 'kizeo_last_import_ts';
    const freqMs       = {{ config('vits.kizeo_frequence_min', 60) }} * 60 * 1000;
    const serverTs     = {{ $derniereImport ? $derniereImport->timestamp * 1000 : 'null' }};
    const el           = document.getElementById('kizeo-countdown');
    if (!el) return;

    // Synchroniser localStorage avec la valeur serveur (la plus récente gagne)
    const storedTs = parseInt(localStorage.getItem(STORAGE_KEY) || '0');
    let lastTs = Math.max(storedTs, serverTs || 0);
    if (serverTs) localStorage.setItem(STORAGE_KEY, serverTs);

    // Si jamais importé, on ne peut pas faire de countdown fiable
    if (!lastTs) {
        el.textContent = '—';
        return;
    }

    const nextTs = lastTs + freqMs;

    function tick() {
        const reste = Math.max(0, nextTs - Date.now());
        const m = Math.floor(reste / 60000);
        const s = Math.floor((reste % 60000) / 1000);
        el.textContent = m + 'min ' + String(s).padStart(2, '0') + 's';
        if (reste === 0) {
            el.textContent = '⟳ en cours…';
            clearInterval(timer);
            setTimeout(() => window.location.reload(), 5000);
        }
    }

    const timer = setInterval(tick, 1000);
    tick();
})();
</script>

{{-- GRILLE ALERTES --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">

    {{-- Contrats expirés --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#dc2626">⚠</span> Contrats terminés non renouvelés
            </div>
            <span style="background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $contratsExpires->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @forelse($contratsExpires as $contrat)
            <a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;justify-content:space-between;padding:7px 14px;text-decoration:none;color:#1a1a1a;border-bottom:1px solid #f5f5f5">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#fef2f2;border:1.5px solid #dc2626;display:inline-block"></span>
                    {{ $contrat->client->nom_societe }}
                </div>
                <span style="font-size:11px;color:#aaa">fin le {{ $contrat->date_fin->format('d/m/Y') }}</span>
            </a>
            @empty
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucun contrat expiré</div>
            @endforelse
        </div>
        <a href="{{ route('contrats.index', ['statut' => 'expire']) }}" style="display:block;font-size:11px;color:#E8720C;text-align:right;padding:6px 14px;border-top:1px solid #e0e0e0;text-decoration:none">Voir tous les contrats →</a>
    </div>

    {{-- Contrats échéance --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#E8720C">⏱</span> Contrats arrivant à échéance
            </div>
            <span style="background:#FFF3E6;color:#E8720C;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $contratsEcheance->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @forelse($contratsEcheance as $contrat)
            <a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;justify-content:space-between;padding:7px 14px;text-decoration:none;color:#1a1a1a;border-bottom:1px solid #f5f5f5">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#FFF3E6;border:1.5px solid #E8720C;display:inline-block"></span>
                    {{ $contrat->client->nom_societe }}
                </div>
                <span style="font-size:11px;color:#aaa">fin le {{ $contrat->date_fin->format('d/m/Y') }}</span>
            </a>
            @empty
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucun contrat en échéance proche</div>
            @endforelse
        </div>
        <a href="{{ route('contrats.index') }}" style="display:block;font-size:11px;color:#E8720C;text-align:right;padding:6px 14px;border-top:1px solid #e0e0e0;text-decoration:none">Voir tous les contrats →</a>
    </div>

    {{-- Interventions non traitées --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#E8720C">⚡</span> Interventions non traitées
            </div>
            <span style="background:#FFF3E6;color:#E8720C;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $interventionsNonTraitees->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @forelse($interventionsNonTraitees as $intervention)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 14px;border-bottom:1px solid #f5f5f5">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#FFF3E6;border:1.5px solid #E8720C;display:inline-block"></span>
                    {{ $intervention->contrat->client->nom_societe ?? '—' }} — {{ $intervention->type_tri }}
                </div>
                <span style="font-size:11px;color:#aaa">{{ $intervention->date_intervention->format('d/m') }}</span>
            </div>
            @empty
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucune intervention non traitée</div>
            @endforelse
        </div>
    </div>

    {{-- Périodes à régler --}}
    @if($periodesARegler->count() > 0)
    <div style="background:#fff;border:2px solid #E8720C;border-radius:12px;overflow:hidden;margin-bottom:1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #f0e0d0;background:#FFF9F5">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:#E8720C">
                ⚠ Périodes terminées — ajustement requis
            </div>
            <span style="background:#FFF3E6;color:#E8720C;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $periodesARegler->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @foreach($periodesARegler as $item)
            @php
                $solde = $item['solde_minutes'];
                $h = $item['h'];
                $m = $item['m'];
                $dureeLabel = ($h > 0 ? $h.'h' : '').($m > 0 ? $m.'min' : '');
            @endphp
            <div style="padding:10px 14px;border-bottom:1px solid #f5f5f5">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#1a1a1a">
                            {{ $item['contrat']->client->nom_societe }}
                            <span style="font-size:11px;font-family:monospace;color:#888;margin-left:6px">{{ $item['contrat']->numero_contrat_vits }}</span>
                        </div>
                        <div style="font-size:12px;color:#888;margin-top:2px">
                            Période {{ $item['periode']['numero'] }} — terminée le {{ $item['periode']['fin']->format('d/m/Y') }}
                            @if($item['credit'])
                                <span style="color:#166534;font-weight:500">· Crédit : +{{ $dureeLabel }} non consommées</span>
                            @else
                                <span style="color:#dc2626;font-weight:500">· Dépassement : {{ $dureeLabel }} en trop</span>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0">
                        <form method="POST" action="{{ route('periodes.traiter') }}">
                            @csrf
                            <input type="hidden" name="contrat_id"     value="{{ $item['contrat']->id }}">
                            <input type="hidden" name="periode_numero" value="{{ $item['periode']['numero'] }}">
                            <input type="hidden" name="periode_fin"    value="{{ $item['periode']['fin']->format('Y-m-d') }}">
                            <input type="hidden" name="solde_minutes"  value="{{ $solde }}">
                            <input type="hidden" name="action"         value="reporte">
                            <button type="submit" style="padding:5px 10px;font-size:11px;background:#E8720C;color:#fff;border:none;border-radius:6px;cursor:pointer">
                                {{ $item['credit'] ? '↪ Reporter crédit' : '↪ Reporter dépassement' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('periodes.traiter') }}">
                            @csrf
                            <input type="hidden" name="contrat_id"     value="{{ $item['contrat']->id }}">
                            <input type="hidden" name="periode_numero" value="{{ $item['periode']['numero'] }}">
                            <input type="hidden" name="periode_fin"    value="{{ $item['periode']['fin']->format('Y-m-d') }}">
                            <input type="hidden" name="solde_minutes"  value="{{ $solde }}">
                            <input type="hidden" name="action"         value="ignore">
                            <button type="submit" style="padding:5px 10px;font-size:11px;background:#fff;color:#888;border:1px solid #ddd;border-radius:6px;cursor:pointer">
                                Ignorer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Alertes informatives --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #e0e0e0">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500">
                <span style="color:#166534">ℹ</span> Alertes informatives
            </div>
            <span style="background:#f0fdf4;color:#166534;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:500">{{ $contratsBrouillon + $clientsSansInfos + $clientsSansContrat + $contratsDepassement->count() }}</span>
        </div>
        <div style="padding:4px 0">
            @if($contratsBrouillon > 0)
            <a href="{{ route('contrats.index', ['statut' => 'non-actif']) }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $contratsBrouillon }} contrat(s) non actif(s) à valider
            </a>
            @endif
            @if($clientsSansInfos > 0)
            <a href="{{ route('clients.index') }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $clientsSansInfos }} client(s) avec infos incomplètes
            </a>
            @endif
            @if($clientsSansContrat > 0)
            <a href="{{ route('clients.index') }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $clientsSansContrat }} client(s) sans aucun contrat
            </a>
            @endif
            @foreach($contratsDepassement as $contrat)
            <a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;gap:6px;padding:7px 14px;font-size:12px;color:#1a1a1a;text-decoration:none;border-bottom:1px solid #f5f5f5">
                <span style="width:7px;height:7px;border-radius:50%;background:#f0fdf4;border:1.5px solid #166534;display:inline-block"></span>
                {{ $contrat->client->nom_societe }} — dépassement d'heures
            </a>
            @endforeach
            @if($contratsBrouillon + $clientsSansInfos + $clientsSansContrat + $contratsDepassement->count() === 0)
            <div style="padding:12px 14px;font-size:12px;color:#aaa;text-align:center">Aucune alerte informative</div>
            @endif
        </div>
    </div>

</div>
@endsection
