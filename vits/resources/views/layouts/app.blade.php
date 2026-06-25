<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIT-S — @yield('title', 'Application interne')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Arial,sans-serif;background:#f5f5f5;display:flex;min-height:100vh}
        .sidebar{width:200px;background:#fff;border-right:1px solid #e0e0e0;padding:1rem 0;flex-shrink:0;display:flex;flex-direction:column}
        .sidebar-logo{padding:0.75rem 1rem 1.25rem;border-bottom:1px solid #e0e0e0;margin-bottom:0.5rem}
        .logo-badge{background:#E8720C;color:#fff;font-size:13px;font-weight:500;padding:3px 10px;border-radius:6px;display:inline-block}
        .logo-sub{font-size:11px;color:#aaa;margin-top:4px}
        /* -- Sidebar nav -- */
        .nav-section{font-size:10px;color:#bbb;padding:1.1rem 1rem 0.3rem;text-transform:uppercase;letter-spacing:0.6px;font-weight:600}
        /* Thème */
        .theme-section{margin:1px 8px;border-radius:8px}
        .theme-header{display:flex;align-items:center;gap:10px;padding:6px 8px;cursor:pointer;user-select:none;border-radius:8px}
        .theme-header:hover{background:#f5f5f5}
        .theme-section.open>.theme-header{background:#f5f5f5}
        .theme-icon{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px}
        .theme-icon-primary{background:#E8720C;color:#fff}
        .theme-icon-neutral{background:#f0f0f0;border:1px solid #e4e4e4;color:#888}
        .theme-section.open.has-active>.theme-header>.theme-icon-neutral{background:#E8720C;color:#fff;border-color:#E8720C}
        .theme-label{font-size:12px;font-weight:500;color:#444;flex:1}
        .theme-arrow{font-size:14px;color:#ccc;transition:transform 0.18s;margin-left:auto}
        .theme-section.open>.theme-header>.theme-arrow{transform:rotate(90deg)}
        .theme-items{display:none;padding-bottom:4px}
        .theme-section.open>.theme-items{display:block}
        /* Sous-menu */
        .nav-sub{display:flex;align-items:center;gap:8px;padding:5px 8px 5px 46px;font-size:12px;color:#888;text-decoration:none;border-radius:6px;margin:1px 0}
        .nav-sub:hover{background:#f5f5f5;color:#333}
        .nav-sub.active{background:#fff8f3;color:#E8720C;font-weight:500}
        .nav-sub i{font-size:14px;flex-shrink:0}
        .nav-sub-disabled{display:flex;align-items:center;gap:8px;padding:5px 8px 5px 46px;font-size:12px;color:#ccc}
        /* Système */
        .nav-item{display:flex;align-items:center;gap:9px;padding:7px 12px;font-size:12px;color:#666;text-decoration:none;border-radius:6px;margin:1px 8px}
        .nav-item:hover{background:#f5f5f5;color:#1a1a1a}
        .nav-item.active{color:#E8720C;background:#fff8f3;font-weight:500}
        .nav-item i{font-size:15px}
        .sidebar-bottom{margin-top:auto;padding:0.75rem 1rem;border-top:1px solid #e0e0e0}
        .user-row{display:flex;align-items:center;gap:8px}
        .avatar{width:30px;height:30px;border-radius:50%;background:#E8720C;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;flex-shrink:0}
        .user-name{font-size:12px;font-weight:500;color:#1a1a1a}
        .user-role{font-size:11px;color:#aaa}
        .main{flex:1;overflow:auto}
        .content{padding:1.5rem}
    </style>
</head>
<body data-page="{{ request()->route()?->getName() ?? '' }}">
    <div class="sidebar">
        <div class="sidebar-logo">
            @php $logoPath = file_exists(public_path('storage/logo/logo.png')) ? asset('storage/logo/logo.png') : null; @endphp
            @if($logoPath)
                <img src="{{ $logoPath }}" style="max-height:38px;max-width:140px;object-fit:contain">
            @else
                <div class="logo-badge">VIT-S</div>
            @endif
            <div class="logo-sub">Application interne</div>
        </div>

        @php
            $user = auth()->user();
            $inInterventions = request()->routeIs('dashboard', 'clients.*', 'contrats.*', 'interventions.*', 'performance.*', 'clients.fusion*');
            $inCalculs       = request()->routeIs('bitdefender.*');
            $inFacturation   = false;
        @endphp

        {{-- THÈME INTERVENTIONS --}}
        @if($user->hasTheme('interventions'))
        <div class="theme-section {{ $inInterventions ? 'open has-active' : '' }}">
            <div class="theme-header" onclick="this.closest('.theme-section').classList.toggle('open')">
                <span class="theme-icon theme-icon-primary"><i class="ti ti-tool"></i></span>
                <span class="theme-label">Interventions</span>
                <i class="ti ti-chevron-right theme-arrow"></i>
            </div>
            <div class="theme-items">
                <a href="{{ route('dashboard') }}" class="nav-sub {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="ti ti-layout-dashboard"></i> Synthèse
                </a>
                <a href="{{ route('clients.index') }}" class="nav-sub {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="ti ti-users"></i> Clients
                </a>
                <a href="{{ route('contrats.index') }}" class="nav-sub {{ request()->routeIs('contrats.*') ? 'active' : '' }}">
                    <i class="ti ti-file-text"></i> Contrats
                </a>
                <a href="{{ route('interventions.index') }}" class="nav-sub {{ request()->routeIs('interventions.*') ? 'active' : '' }}">
                    <i class="ti ti-list"></i> Interventions
                </a>
                @if($user->isAdmin())
                <a href="{{ route('performance.index') }}" class="nav-sub {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                    <i class="ti ti-chart-bar"></i> Performance
                </a>
                <a href="{{ route('clients.fusion') }}" class="nav-sub {{ request()->routeIs('clients.fusion*') ? 'active' : '' }}">
                    <i class="ti ti-arrows-join"></i> Fusion saisies
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- THÈME CALCULS --}}
        @if($user->hasTheme('calculs'))
        <div class="theme-section {{ $inCalculs ? 'open has-active' : '' }}">
            <div class="theme-header" onclick="this.closest('.theme-section').classList.toggle('open')">
                <span class="theme-icon theme-icon-neutral"><i class="ti ti-calculator"></i></span>
                <span class="theme-label">Calculs</span>
                <i class="ti ti-chevron-right theme-arrow"></i>
            </div>
            <div class="theme-items">
                <a href="{{ route('bitdefender.index') }}" class="nav-sub {{ request()->routeIs('bitdefender.*') ? 'active' : '' }}">
                    <i class="ti ti-shield"></i> Calcul prorata
                </a>
            </div>
        </div>
        @endif

        {{-- THÈME FACTURATION --}}
        @if($user->hasTheme('facturation'))
        <div class="theme-section {{ $inFacturation ? 'open has-active' : '' }}">
            <div class="theme-header" onclick="this.closest('.theme-section').classList.toggle('open')">
                <span class="theme-icon theme-icon-neutral"><i class="ti ti-receipt"></i></span>
                <span class="theme-label">Facturation</span>
                <i class="ti ti-chevron-right theme-arrow"></i>
            </div>
            <div class="theme-items">
                <span class="nav-sub-disabled"><i class="ti ti-clock"></i> Bientôt disponible</span>
            </div>
        </div>
        @endif

        {{-- SYSTÈME --}}
        <div class="nav-section">Système</div>
        @if($user->isAdmin())
        <a href="{{ route('parametres.index') }}" class="nav-item {{ request()->routeIs('parametres.*') && !request()->routeIs('mail-templates.*') ? 'active' : '' }}">
            <i class="ti ti-settings"></i> Paramètres
        </a>
        <a href="{{ route('mail-templates.index') }}" class="nav-item {{ request()->routeIs('mail-templates.*') ? 'active' : '' }}">
            <i class="ti ti-mail"></i> Templates mail
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="ti ti-user-cog"></i> Utilisateurs
        </a>
        <a href="{{ route('admin.clients-tableau.index') }}" class="nav-item {{ request()->routeIs('admin.clients-tableau.*') ? 'active' : '' }}">
            <i class="ti ti-table"></i> Tableau clients
        </a>
        @endif
        <a href="{{ route('logout') }}" class="nav-item"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="ti ti-logout"></i> Déconnexion
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
            @csrf
        </form>

        <div class="sidebar-bottom">
            <div class="user-row">
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ $user->isAdmin() ? 'Admin' : implode(', ', $user->themes ?? ['—']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="main">
        <div class="content">
            @yield('content')
        </div>
    </div>
    @livewireScripts

    {{-- Modal avertissement déconnexion --}}
    <div id="modal-inactivite" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:12px;padding:2rem;max-width:380px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.18)">
            <div style="font-size:2rem;margin-bottom:0.75rem">⏱️</div>
            <div style="font-size:15px;font-weight:600;color:#1a1a1a;margin-bottom:0.5rem">Session sur le point d'expirer</div>
            <div style="font-size:13px;color:#888;margin-bottom:1.25rem">
                Vous serez déconnecté dans <span id="compte-a-rebours" style="font-weight:700;color:#E8720C">2:00</span> en raison d'inactivité.
            </div>
            <button onclick="resetInactivite()" style="padding:9px 24px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer">
                Je suis là
            </button>
        </div>
    </div>

    <script>
    (function() {
        const TIMEOUT_MS  = {{ config('vits.session_minutes', 30) }} * 60 * 1000;
        const WARNING_MS  = Math.min(2 * 60 * 1000, TIMEOUT_MS * 0.2); // 2 min ou 20% si timeout court
        const STORAGE_KEY = 'vits_last_activity';
        let warningTimer  = null;
        let logoutTimer   = null;
        let countdownInterval = null;

        // Vérification au chargement : si l'app a été quittée et le timeout est dépassé
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored && (Date.now() - parseInt(stored)) >= TIMEOUT_MS) {
            localStorage.removeItem(STORAGE_KEY);
            document.getElementById('logout-form').submit();
        }

        function resetInactivite() {
            localStorage.setItem(STORAGE_KEY, Date.now());
            document.getElementById('modal-inactivite').style.display = 'none';
            clearTimeout(warningTimer);
            clearTimeout(logoutTimer);
            clearInterval(countdownInterval);
            planifierTimers();
        }
        window.resetInactivite = resetInactivite;

        function planifierTimers() {
            warningTimer = setTimeout(afficherAvertissement, TIMEOUT_MS - WARNING_MS);
            logoutTimer  = setTimeout(deconnecter, TIMEOUT_MS);
        }

        function afficherAvertissement() {
            document.getElementById('modal-inactivite').style.display = 'flex';
            let secondes = Math.floor(WARNING_MS / 1000);
            document.getElementById('compte-a-rebours').textContent = formatCountdown(secondes);
            countdownInterval = setInterval(function() {
                secondes--;
                if (secondes <= 0) clearInterval(countdownInterval);
                else document.getElementById('compte-a-rebours').textContent = formatCountdown(secondes);
            }, 1000);
        }

        function formatCountdown(s) {
            const m = Math.floor(s / 60);
            const sec = s % 60;
            return m + ':' + String(sec).padStart(2, '0');
        }

        function deconnecter() {
            localStorage.removeItem(STORAGE_KEY);
            document.getElementById('logout-form').submit();
        }

        // Enregistrer l'heure de départ quand on quitte / cache l'onglet
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                localStorage.setItem(STORAGE_KEY, Date.now());
            } else {
                // Retour sur l'app : vérifier si le timeout est dépassé
                const ts = localStorage.getItem(STORAGE_KEY);
                if (ts && (Date.now() - parseInt(ts)) >= TIMEOUT_MS) {
                    deconnecter();
                } else {
                    resetInactivite();
                }
            }
        });

        ['mousemove','keydown','click','scroll','touchstart'].forEach(function(e) {
            document.addEventListener(e, resetInactivite, { passive: true });
        });

        resetInactivite();
    })();
    </script>

    <script>
(function() {
    const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content;
    const PAGE   = document.body.dataset.page || '';
    let saveTimer = null;

    function schedulePreferenceSave(table) {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function() {
            const key   = PAGE + '.' + (table.dataset.tableId || 'table0');
            const ths   = Array.from(table.querySelectorAll('thead th'));
            const widths = {}, order = [];
            ths.forEach(function(th) {
                const orig = th.dataset.origCol;
                if (orig === undefined) return;
                order.push(parseInt(orig));
                if (th.style.width) widths[orig] = th.style.width;
            });
            fetch('/preferences', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ page: key, preferences: { widths, order } })
            });
        }, 500);
    }

    function applyPreferences(table, prefs) {
        if (!prefs) return;
        const ths = Array.from(table.querySelectorAll('thead th'));

        if (prefs.widths) {
            ths.forEach(function(th) {
                const w = prefs.widths[th.dataset.origCol];
                if (w) th.style.width = w;
            });
        }

        if (prefs.order && prefs.order.length === ths.length) {
            const origToPos = {};
            ths.forEach(function(th, pos) { origToPos[parseInt(th.dataset.origCol)] = pos; });
            const reorderMap = prefs.order.map(function(orig) { return origToPos[orig]; })
                                          .filter(function(p) { return p !== undefined; });
            if (reorderMap.length === ths.length) {
                table.querySelectorAll('tr').forEach(function(row) {
                    const cells = Array.from(row.children);
                    if (cells.length < reorderMap.length) return;
                    reorderMap.forEach(function(fromPos) { row.appendChild(cells[fromPos]); });
                });
            }
        }
    }

    function loadPreferences(table) {
        const key = PAGE + '.' + (table.dataset.tableId || 'table0');
        fetch('/preferences/' + encodeURIComponent(key))
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (data && data.preferences) applyPreferences(table, data.preferences);
            })
            .catch(function() {});
    }

    function makeResizable(table) {
        table.querySelectorAll('th').forEach(function(th) {
            th.style.position = 'relative';
            const resizer = document.createElement('div');
            resizer.style.cssText = 'position:absolute;right:0;top:20%;bottom:20%;width:4px;cursor:col-resize;user-select:none;z-index:2;background:#ddd;border-radius:2px;opacity:0.6';
            resizer.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const startX = e.pageX, startW = th.offsetWidth;
                function onMove(e) { th.style.width = Math.max(50, startW + e.pageX - startX) + 'px'; }
                function onUp() {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    schedulePreferenceSave(table);
                }
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });
            th.appendChild(resizer);
        });
    }

    function makeSortable(table) {
        const thead = table.querySelector('thead tr');
        if (!thead) return;
        const ths = Array.from(thead.querySelectorAll('th'));
        let dragSrc = null, isDragging = false;

        ths.forEach(function(th, idx) {
            if (!th.textContent.trim()) return;
            const handle = document.createElement('span');
            handle.innerHTML = '⠿';
            handle.title = 'Déplacer la colonne';
            handle.style.cssText = 'cursor:grab;color:#ccc;font-size:12px;margin-left:4px;user-select:none;display:inline-block';
            handle.draggable = true;
            th.appendChild(handle);

            handle.addEventListener('dragstart', function(e) {
                dragSrc = idx; isDragging = true;
                th.style.opacity = '0.5';
                e.dataTransfer.effectAllowed = 'move';
            });
            handle.addEventListener('dragend', function() {
                th.style.opacity = '1'; isDragging = false; dragSrc = null;
            });
            th.addEventListener('dragover', function(e) {
                if (!isDragging) return;
                e.preventDefault(); th.style.background = '#fff0e0';
            });
            th.addEventListener('dragleave', function() { th.style.background = ''; });
            th.addEventListener('drop', function(e) {
                if (!isDragging) return;
                e.preventDefault(); th.style.background = '';
                if (dragSrc === null || dragSrc === idx) return;
                const src = dragSrc;
                table.querySelectorAll('tr').forEach(function(row) {
                    const cells = Array.from(row.children);
                    if (cells.length <= Math.max(src, idx)) return;
                    const dragCell = cells[src], targetCell = cells[idx];
                    if (src < idx) row.insertBefore(dragCell, targetCell.nextSibling);
                    else row.insertBefore(dragCell, targetCell);
                });
                dragSrc = null; isDragging = false;
                schedulePreferenceSave(table);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        let tableIndex = 0;
        document.querySelectorAll('table').forEach(function(table) {
            if (table.closest('form')) return;
            table.dataset.tableId = 'table' + tableIndex++;
            table.querySelectorAll('thead th').forEach(function(th, i) {
                th.dataset.origCol = i;
            });
            makeResizable(table);
            makeSortable(table);
            loadPreferences(table);
        });
    });
})();
    </script>
</body>
</html>
