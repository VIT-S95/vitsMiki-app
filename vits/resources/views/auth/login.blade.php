<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIT-S — Connexion</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 2rem 2.5rem; width: 100%; max-width: 380px; }
        .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 2rem; justify-content: center; }
        .logo-badge { background: #E8720C; color: #fff; font-size: 18px; font-weight: 500; padding: 6px 14px; border-radius: 8px; letter-spacing: 1px; }
        .logo-sub { font-size: 13px; color: #888; }
        .field { margin-bottom: 1rem; }
        .field label { display: block; font-size: 13px; color: #666; margin-bottom: 6px; }
        .field input { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .field input:focus { outline: none; border-color: #E8720C; box-shadow: 0 0 0 2px rgba(232,114,12,0.15); }
        .btn { width: 100%; padding: 9px; background: #E8720C; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; margin-top: 0.5rem; }
        .btn:hover { background: #c9620a; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; border-radius: 8px; padding: 8px 12px; font-size: 13px; margin-bottom: 1rem; }
        .hint { font-size: 12px; color: #aaa; text-align: center; margin-top: 1rem; }
        .divider { border: none; border-top: 1px solid #eee; margin: 1.5rem 0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <div class="logo-badge">VIT-S</div>
            <div class="logo-sub">Application interne</div>
        </div>

        @if ($errors->any())
            <div class="error">Email ou mot de passe incorrect.</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="field">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="votre@email.fr" required autofocus>
            </div>
            <div class="field">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Se connecter</button>
        </form>

        <hr class="divider">
        <p class="hint">Accès réservé à l'équipe VIT-S</p>
    </div>
</body>
</html>