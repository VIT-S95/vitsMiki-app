<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIT-S — @yield('title', 'Application interne')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Arial,sans-serif;background:#f5f5f5;display:flex;min-height:100vh}
        .sidebar{width:200px;background:#fff;border-right:1px solid #e0e0e0;padding:1rem 0;flex-shrink:0;display:flex;flex-direction:column}
        .sidebar-logo{padding:0.75rem 1rem 1.25rem;border-bottom:1px solid #e0e0e0;margin-bottom:0.5rem}
        .logo-badge{background:#E8720C;color:#fff;font-size:13px;font-weight:500;padding:3px 10px;border-radius:6px;display:inline-block}
        .logo-sub{font-size:11px;color:#aaa;margin-top:4px}
        .nav-section{font-size:10px;color:#aaa;padding:1rem 1rem 0.25rem;text-transform:uppercase;letter-spacing:0.5px}
        .nav-item{display:flex;align-items:center;gap:9px;padding:8px 1rem;font-size:13px;color:#666;cursor:pointer;border-left:2px solid transparent;text-decoration:none}
        .nav-item:hover{background:#f5f5f5;color:#1a1a1a}
        .nav-item.active{color:#E8720C;border-left:2px solid #E8720C;background:#fff8f3;font-weight:500}
        .nav-item i{font-size:16px}
        .sidebar-bottom{margin-top:auto;padding:0.75rem 1rem;border-top:1px solid #e0e0e0}
        .user-row{display:flex;align-items:center;gap:8px}
        .avatar{width:30px;height:30px;border-radius:50%;background:#E8720C;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;flex-shrink:0}
        .user-name{font-size:12px;font-weight:500;color:#1a1a1a}
        .user-role{font-size:11px;color:#aaa}
        .main{flex:1;overflow:auto}
        .content{padding:1.5rem}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-badge">VIT-S</div>
            <div class="logo-sub">Application interne</div>
        </div>

        <div class="nav-section">Navigation</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="ti ti-layout-dashboard"></i> Tableau de bord
        </a>
        <a href="{{ route('clients.index') }}" class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="ti ti-users"></i> Clients
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('contrats.*') ? 'active' : '' }}">
            <i class="ti ti-file-text"></i> Contrats
        </a>
        <a href="#" class="nav-item {{ request()->routeIs('interventions.*') ? 'active' : '' }}">
            <i class="ti ti-tool"></i> Interventions
        </a>

        <div class="nav-section">Système</div>
        <a href="#" class="nav-item {{ request()->routeIs('parametres.*') ? 'active' : '' }}">
            <i class="ti ti-settings"></i> Paramètres
        </a>
        <a href="{{ route('logout') }}" class="nav-item"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="ti ti-logout"></i> Déconnexion
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
            @csrf
        </form>

        <div class="sidebar-bottom">
            <div class="user-row">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->getRoleNames()->first() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>