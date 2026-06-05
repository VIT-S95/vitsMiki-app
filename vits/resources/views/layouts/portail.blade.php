<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portail client') — VIT-S</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f7f7f5; color: #1a1a1a; min-height: 100vh; display: flex; flex-direction: column; }
        .p-header { background: #fff; border-bottom: 1px solid #e0e0e0; padding: 0 2rem; height: 56px; display: flex; align-items: center; justify-content: space-between; }
        .p-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .p-logo-badge { width: 32px; height: 32px; border-radius: 8px; background: #E8720C; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; }
        .p-logo-name { font-size: 15px; font-weight: 600; color: #1a1a1a; }
        .p-client-name { font-size: 13px; color: #888; display: flex; align-items: center; gap: 8px; }
        .p-client-badge { background: #FFF3E6; color: #E8720C; border: 1px solid #E8720C; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 500; }
        .p-nav { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 0 2rem; display: flex; gap: 0; }
        .p-nav a { display: inline-flex; align-items: center; gap: 6px; padding: 12px 16px; font-size: 13px; color: #888; text-decoration: none; border-bottom: 2px solid transparent; transition: color .15s, border-color .15s; }
        .p-nav a:hover, .p-nav a.active { color: #E8720C; border-bottom-color: #E8720C; }
        .p-main { flex: 1; padding: 2rem; max-width: 1100px; width: 100%; margin: 0 auto; }
        .p-footer { padding: 1rem 2rem; text-align: center; font-size: 11px; color: #bbb; border-top: 1px solid #f0f0f0; background: #fff; }
        .p-logout { font-size: 12px; color: #aaa; text-decoration: none; padding: 4px 10px; border: 1px solid #e0e0e0; border-radius: 6px; }
        .p-logout:hover { color: #E8720C; border-color: #E8720C; }
    </style>
    @stack('styles')
</head>
<body>
    <header class="p-header">
        <a href="{{ route('portail.index') }}" class="p-logo">
            @if(file_exists(public_path('storage/logo/logo.png')))
                <img src="{{ asset('storage/logo/logo.png') }}" style="height:28px;width:auto;object-fit:contain">
            @else
                <div class="p-logo-badge">V</div>
                <span class="p-logo-name">VIT-S</span>
            @endif
        </a>
        <div class="p-client-name">
            <span class="p-client-badge">Portail client</span>
            <span>{{ auth()->user()->client?->nom_societe ?? auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="p-logout">Déconnexion</button>
            </form>
        </div>
    </header>

    <nav class="p-nav">
        <a href="{{ route('portail.index') }}" class="{{ request()->routeIs('portail.index') ? 'active' : '' }}">Tableau de bord</a>
        <a href="#" class="{{ request()->routeIs('portail.interventions*') ? 'active' : '' }}">Interventions</a>
        <a href="#" class="{{ request()->routeIs('portail.contrats*') ? 'active' : '' }}">Contrats</a>
        <a href="#" class="{{ request()->routeIs('portail.documents*') ? 'active' : '' }}">Documents</a>
    </nav>

    <main class="p-main">
        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:8px;margin-bottom:1rem;font-size:13px">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;margin-bottom:1rem;font-size:13px">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="p-footer">
        VIT-S · Solutions informatiques &amp; maintenance · Portail client
    </footer>
</body>
</html>
