@extends('layouts.app')
@section('title', 'Parametres')
@section('content')
<div style="margin-bottom:1.5rem">
    <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Parametres</h1>
    <p style="font-size:13px;color:#888;margin-top:2px">Configuration des seuils d alerte, Kizeo et motifs d intervention</p>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem">{{ session('success') }}</div>
@endif

<style>
.acc-item{background:#fff;border:1px solid #e0e0e0;border-radius:12px;margin-bottom:8px;overflow:hidden}
.acc-header{display:flex;align-items:center;justify-content:flex-start;gap:10px;padding:12px 16px;background:#f8f8f8;cursor:pointer;user-select:none;border:none;width:100%;text-align:left}
.acc-header:hover{background:#f2f2f2}
.acc-title{font-size:13px;font-weight:500;color:#1a1a1a}
.acc-chevron{color:#E8720C;font-size:13px;transition:transform 0.25s ease;display:inline-block;line-height:1}
.acc-chevron.open{transform:rotate(90deg)}
.acc-body{overflow:hidden;max-height:0;transition:max-height 0.3s ease}
</style>

{{-- FORM 1 : Alertes + Kizeo + Logo --}}
<form method="POST" action="{{ route('parametres.update') }}" enctype="multipart/form-data">
@csrf

{{-- 1. Alertes & Seuils --}}
<div class="acc-item">
    <button type="button" class="acc-header" onclick="toggleAcc(this)">
        <span class="acc-chevron">&#9654;</span>
        <span class="acc-title">Alertes &amp; Seuils</span>
    </button>
    <div class="acc-body">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
            <div>
                <div style="font-size:13px;color:#1a1a1a">Alerte echeance contrat</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">Delai avant la fin du contrat a partir duquel l alerte orange s affiche</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <input type="number" name="seuil_echeance_mois" value="{{ $parametres['seuil_echeance_mois'] }}" min="1" max="12" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                <span style="font-size:12px;color:#888">mois</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
            <div>
                <div style="font-size:13px;color:#1a1a1a">Seuil depassement d heures</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">Pourcentage des heures allouees consommees declenchant l alerte</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <input type="number" name="seuil_heures_pct" value="{{ $parametres['seuil_heures_pct'] }}" min="50" max="100" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                <span style="font-size:12px;color:#888">%</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px">
            <div>
                <div style="font-size:13px;color:#1a1a1a">Expiration de session</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">Durée d'inactivité avant déconnexion automatique (fermeture de l'app incluse)</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <input type="number" name="session_minutes" value="{{ $parametres['session_minutes'] }}" min="5" max="480" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                <span style="font-size:12px;color:#888">min</span>
            </div>
        </div>
    </div>
</div>

{{-- 2. Synchronisation Kizeo --}}
<div class="acc-item">
    <button type="button" class="acc-header" onclick="toggleAcc(this)">
        <span class="acc-chevron">&#9654;</span>
        <span class="acc-title">Synchronisation Kizeo</span>
    </button>
    <div class="acc-body">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
            <div>
                <div style="font-size:13px;color:#1a1a1a">Frequence d import</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">Intervalle entre deux synchronisations automatiques</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <input type="number" name="kizeo_frequence_min" value="{{ $parametres['kizeo_frequence_min'] }}" min="5" max="120" style="width:60px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                <span style="font-size:12px;color:#888">min</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px">
            <div>
                <div style="font-size:13px;color:#1a1a1a">Cle API Kizeo</div>
                <div style="font-size:12px;color:#aaa;margin-top:2px">Token d authentification pour l acces a l API Kizeo Forms</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <input type="password" name="kizeo_api_key" value="{{ $parametres['kizeo_api_key'] }}" placeholder="kz_live_xxxx" id="api-key" style="width:200px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                <button type="button" onclick="document.getElementById('api-key').type=document.getElementById('api-key').type==='password'?'text':'password'" style="background:none;border:none;cursor:pointer;font-size:16px;color:#888">&#128065;</button>
            </div>
        </div>
    </div>
</div>

{{-- 3. Logo VIT-S --}}
<div class="acc-item">
    <button type="button" class="acc-header" onclick="toggleAcc(this)">
        <span class="acc-chevron">&#9654;</span>
        <span class="acc-title">Logo VIT-S</span>
    </button>
    <div class="acc-body">
        <div style="padding:12px 16px;display:flex;align-items:center;gap:16px">
            @if($logoPath)
                <img src="{{ $logoPath }}" style="height:48px;object-fit:contain;border:1px solid #eee;border-radius:6px;padding:4px">
                <div>
                    <div style="font-size:12px;color:#166534;margin-bottom:6px">&#10003; Logo actuel</div>
                    <button type="button" onclick="document.getElementById('form-delete-logo').submit()" style="font-size:11px;color:#dc2626;background:#fff;border:1px solid #fca5a5;padding:4px 10px;border-radius:6px;cursor:pointer">&#128465; Supprimer</button>
                </div>
            @else
                <div style="width:80px;height:48px;border:2px dashed #ddd;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:11px">Aucun</div>
            @endif
            <div style="flex:1">
                <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">{{ $logoPath ? 'Remplacer le logo' : 'Ajouter un logo' }}</label>
                <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml" style="font-size:12px;color:#666">
                <div style="font-size:11px;color:#aaa;margin-top:3px">PNG, JPG ou SVG — max 2 Mo</div>
            </div>
        </div>
        <div style="padding:10px 16px;border-top:1px solid #e0e0e0;display:flex;justify-content:flex-end">
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">Enregistrer</button>
        </div>
    </div>
</div>
</form>

{{-- 4. Configuration Mail --}}
<div class="acc-item">
    <button type="button" class="acc-header" onclick="toggleAcc(this)">
        <span class="acc-chevron">&#9654;</span>
        <span class="acc-title">Configuration Mail</span>
    </button>
    <div class="acc-body">
        <form method="POST" action="{{ route('parametres.mail.update') }}">
        @csrf
        <div style="padding:10px 16px;border-bottom:1px solid #e0e0e0;display:flex;justify-content:flex-end">
            <a href="{{ route('mail-templates.index') }}" style="font-size:12px;color:#E8720C;text-decoration:none;border:1px solid #E8720C;padding:4px 10px;border-radius:6px">Templates mail</a>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0;border-right:1px solid #e0e0e0">
                <div>
                    <div style="font-size:13px;color:#1a1a1a">Serveur SMTP</div>
                    <div style="font-size:12px;color:#aaa;margin-top:2px">Hôte du serveur mail sortant</div>
                </div>
                <input type="text" name="mail_host" value="{{ $mailSettings['mail_host'] }}" placeholder="smtp.example.com"
                       style="width:180px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
                <div>
                    <div style="font-size:13px;color:#1a1a1a">Port SMTP</div>
                    <div style="font-size:12px;color:#aaa;margin-top:2px">587 (TLS) ou 465 (SSL)</div>
                </div>
                <input type="number" name="mail_port" value="{{ $mailSettings['mail_port'] }}" min="1" max="65535"
                       style="width:80px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0;border-right:1px solid #e0e0e0">
                <div>
                    <div style="font-size:13px;color:#1a1a1a">Identifiant SMTP</div>
                    <div style="font-size:12px;color:#aaa;margin-top:2px">Nom d'utilisateur de connexion</div>
                </div>
                <input type="text" name="mail_username" value="{{ $mailSettings['mail_username'] }}" placeholder="user@example.com"
                       style="width:180px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
                <div>
                    <div style="font-size:13px;color:#1a1a1a">Mot de passe SMTP</div>
                    <div style="font-size:12px;color:#aaa;margin-top:2px">Mot de passe ou token d'application</div>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    <input type="password" name="mail_password" value="{{ $mailSettings['mail_password'] }}" id="mail-pwd"
                           style="width:160px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <button type="button" onclick="var i=document.getElementById('mail-pwd');i.type=i.type==='password'?'text':'password'"
                            style="background:none;border:none;cursor:pointer;font-size:15px;color:#888">&#128065;</button>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0;border-right:1px solid #e0e0e0">
                <div>
                    <div style="font-size:13px;color:#1a1a1a">Email expéditeur</div>
                    <div style="font-size:12px;color:#aaa;margin-top:2px">Adresse affichée dans le champ "De"</div>
                </div>
                <input type="email" name="mail_from_address" value="{{ $mailSettings['mail_from_address'] }}" placeholder="noreply@vit-s.com"
                       style="width:180px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #e0e0e0">
                <div>
                    <div style="font-size:13px;color:#1a1a1a">Nom expéditeur</div>
                    <div style="font-size:12px;color:#aaa;margin-top:2px">Nom affiché dans le champ "De"</div>
                </div>
                <input type="text" name="mail_from_name" value="{{ $mailSettings['mail_from_name'] }}" placeholder="VIT-S"
                       style="width:180px;padding:6px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
            </div>
        </div>

        <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0">
            <div style="font-size:13px;color:#1a1a1a;margin-bottom:6px">Signature email</div>
            <div style="display:flex;gap:12px">
                <textarea name="mail_signature" id="mail-signature" rows="5"
                          placeholder="Cordialement,&#10;L'équipe VIT-S&#10;Tél : 01 23 45 67 89"
                          oninput="document.getElementById('mail-sig-preview').innerHTML=this.value.replace(/\n/g,'<br>')"
                          style="flex:1;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical;font-family:Arial,sans-serif">{{ $mailSettings['mail_signature'] }}</textarea>
                <div style="flex:1;border:1px solid #e0e0e0;border-radius:8px;padding:10px;font-size:13px;color:#555;background:#fafafa;min-height:90px">
                    <div style="font-size:11px;color:#aaa;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.4px">Aperçu</div>
                    <div id="mail-sig-preview" style="white-space:pre-wrap">{!! nl2br(e($mailSettings['mail_signature'])) !!}</div>
                </div>
            </div>
        </div>

        <div style="padding:10px 16px;display:flex;align-items:center;justify-content:space-between">
            <button type="button" id="btn-test-smtp"
                    onclick="testSmtp()"
                    style="padding:7px 14px;font-size:12px;background:#fff;border:1px solid #ddd;border-radius:8px;cursor:pointer;color:#555">
                Tester la connexion SMTP
            </button>
            <span id="smtp-test-result" style="font-size:12px;margin:0 12px;flex:1"></span>
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">
                Enregistrer la config mail
            </button>
        </div>
        </form>
    </div>
</div>

{{-- 5. Motifs d'intervention --}}
<div class="acc-item" style="margin-bottom:1rem">
    <button type="button" class="acc-header" onclick="toggleAcc(this)">
        <span class="acc-chevron">&#9654;</span>
        <span class="acc-title">Motifs d'intervention</span>
    </button>
    <div class="acc-body">
        <form method="POST" action="{{ route('parametres.motifs') }}">
            @csrf
            <div style="padding:12px 16px" id="motifs-list">
                @foreach($motifs as $motif)
                <div style="display:flex;gap:8px;margin-bottom:6px">
                    <input type="text" name="motifs[]" value="{{ $motif }}" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <button type="button" onclick="this.parentElement.remove()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#dc2626;cursor:pointer">X</button>
                </div>
                @endforeach
            </div>
            <div style="padding:0 16px 14px;display:flex;gap:8px">
                <input type="text" id="new-motif" placeholder="Nouveau motif..." style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                <button type="button" onclick="addMotif()" style="padding:7px 12px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer">+ Ajouter</button>
            </div>
            <div style="padding:10px 16px;border-top:1px solid #e0e0e0;display:flex;justify-content:flex-end">
                <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">Enregistrer les motifs</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAcc(btn) {
    const body = btn.nextElementSibling;
    const chevron = btn.querySelector('.acc-chevron');
    const isOpen = chevron.classList.contains('open');

    document.querySelectorAll('.acc-chevron.open').forEach(function(c) {
        c.classList.remove('open');
        var b = c.closest('.acc-item').querySelector('.acc-body');
        b.style.maxHeight = '0';
    });

    if (!isOpen) {
        chevron.classList.add('open');
        body.style.maxHeight = body.scrollHeight + 'px';
        body.addEventListener('transitionend', function onEnd() {
            if (body.style.maxHeight !== '0px') body.style.maxHeight = 'none';
            body.removeEventListener('transitionend', onEnd);
        });
    }
}

function testSmtp() {
    const btn = document.getElementById('btn-test-smtp');
    const res = document.getElementById('smtp-test-result');
    btn.disabled = true;
    btn.textContent = 'Test en cours...';
    res.textContent = '';
    fetch('{{ route('parametres.mail.test') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        res.textContent = data.message;
        res.style.color = data.ok ? '#166534' : '#dc2626';
    })
    .catch(() => { res.textContent = 'Erreur réseau.'; res.style.color = '#dc2626'; })
    .finally(() => { btn.disabled = false; btn.textContent = 'Tester la connexion SMTP'; });
}

function addMotif(){
    const val = document.getElementById('new-motif').value.trim();
    if (!val) return;
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:6px';
    div.innerHTML = '<input type="text" name="motifs[]" value="'+val+'" style="flex:1;padding:7px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px"><button type="button" onclick="this.parentElement.remove()" style="padding:7px 10px;border:1px solid #ddd;border-radius:8px;background:#fff;color:#dc2626;cursor:pointer">X</button>';
    document.getElementById('motifs-list').appendChild(div);
    document.getElementById('new-motif').value = '';
}
</script>
<form id="form-delete-logo" method="POST" action="{{ route('parametres.logo.delete') }}">
    @csrf @method('DELETE')
</form>
@endsection
