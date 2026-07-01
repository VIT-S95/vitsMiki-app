@extends('layouts.app')
@section('title', 'Tableau clients')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
    <div>
        <h1 style="font-size:18px;font-weight:500;color:#1a1a1a">Tableau clients</h1>
        <p style="font-size:13px;color:#888;margin-top:2px">{{ $clients->count() }} client(s) — clic sur une cellule pour modifier</p>
    </div>
    <button id="btn-save-all" onclick="sauvegarderTout()" style="padding:8px 16px;background:#E8720C;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer">Tout sauvegarder</button>
</div>

<div id="flash" style="display:none;border-radius:8px;padding:8px 14px;font-size:13px;margin-bottom:1rem"></div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead style="background:#f5f5f5">
            <tr>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">ID</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Société</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Email signataire</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Signataire</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">N° client Kizeo</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Statut</th>
                <th style="padding:9px 14px;text-align:center;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Alerte fin contrat</th>
                <th style="padding:9px 14px;text-align:center;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Rapport périodique</th>
                <th style="padding:9px 14px;text-align:left;font-size:11px;color:#888;text-transform:uppercase;border-bottom:1px solid #e0e0e0">Fréquence mail</th>
                <th style="padding:9px 14px;border-bottom:1px solid #e0e0e0"></th>
            </tr>
        </thead>
        <tbody id="tableau-body">
            @foreach($clients as $client)
            <tr data-id="{{ $client->id }}" style="border-bottom:1px solid #f0f0f0;transition:background-color .15s">
                <td style="padding:10px 14px;color:#888;font-family:monospace;font-size:12px">{{ $client->id }}</td>
                <td contenteditable="true" data-field="nom_societe" oninput="marquerModifie(this)" style="padding:10px 14px;font-weight:500;min-width:140px">{{ $client->nom_societe }}</td>
                <td contenteditable="true" data-field="email_signataire" oninput="marquerModifie(this)" style="padding:10px 14px;color:#555;min-width:160px">{{ $client->email_signataire }}</td>
                <td contenteditable="true" data-field="nom_signataire" oninput="marquerModifie(this)" style="padding:10px 14px;color:#555;min-width:140px">{{ $client->nom_signataire }}</td>
                <td contenteditable="true" data-field="numero_client_kizeo" oninput="marquerModifie(this)" style="padding:10px 14px;color:#555;font-family:monospace;font-size:12px;min-width:100px">{{ $client->numero_client_kizeo }}</td>
                <td style="padding:6px 10px">
                    <select data-field="statut" onchange="marquerModifie(this)" style="width:100%;padding:5px 8px;border:1px solid #ddd;border-radius:6px;font-size:12px">
                        <option value="actif" {{ $client->statut === 'actif' ? 'selected' : '' }}>Sous contrat</option>
                        <option value="sans_contrat" {{ $client->statut === 'sans_contrat' ? 'selected' : '' }}>Sans contrat</option>
                        <option value="inactif" {{ $client->statut === 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </td>
                <td style="padding:10px 14px;text-align:center">
                    <input type="checkbox" data-field="mail_alerte_fin_contrat" onchange="marquerModifie(this)" {{ $client->mail_alerte_fin_contrat ? 'checked' : '' }}>
                </td>
                <td style="padding:10px 14px;text-align:center">
                    <input type="checkbox" data-field="mail_rapport_periodique" onchange="marquerModifie(this)" {{ $client->mail_rapport_periodique ? 'checked' : '' }}>
                </td>
                <td style="padding:6px 10px">
                    <select data-field="mail_frequence" onchange="marquerModifie(this)" style="width:100%;padding:5px 8px;border:1px solid #ddd;border-radius:6px;font-size:12px">
                        <option value="mensuel"     {{ $client->mail_frequence === 'mensuel'     ? 'selected' : '' }}>Mensuel</option>
                        <option value="hebdo"       {{ $client->mail_frequence === 'hebdo'       ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="trimestriel" {{ $client->mail_frequence === 'trimestriel' ? 'selected' : '' }}>Trimestriel</option>
                    </select>
                </td>
                <td style="padding:10px 14px;text-align:right">
                    <button onclick="sauvegarderLigne(this.closest('tr')).then(() => afficherFlash('Client sauvegardé.', true)).catch(err => afficherFlash(err.message || 'Erreur réseau.', false))" style="padding:5px 12px;background:#fff;color:#E8720C;border:1px solid #E8720C;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;white-space:nowrap">Sauvegarder</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const URL_BASE   = '{{ url('/admin/clients-tableau') }}';

function marquerModifie(el) {
    const tr = el.closest('tr');
    tr.dataset.dirty = '1';
    tr.style.backgroundColor = '#fef9c3';
}

function demarquerModifie(tr) {
    delete tr.dataset.dirty;
    tr.style.backgroundColor = '';
}

function afficherFlash(message, ok) {
    const flash = document.getElementById('flash');
    flash.style.display = 'block';
    flash.style.background = ok ? '#f0fdf4' : '#fef2f2';
    flash.style.border = '1px solid ' + (ok ? '#bbf7d0' : '#fecaca');
    flash.style.color = ok ? '#166534' : '#991b1b';
    flash.textContent = message;
    setTimeout(() => { flash.style.display = 'none'; }, 4000);
}

function collecterDonnees(tr) {
    const data = {};
    tr.querySelectorAll('[data-field]').forEach(el => {
        const field = el.dataset.field;
        if (el.tagName === 'SELECT') {
            data[field] = el.value;
        } else if (el.type === 'checkbox') {
            data[field] = el.checked;
        } else {
            data[field] = el.textContent.trim();
        }
    });
    return data;
}

function sauvegarderLigne(tr) {
    const id = tr.dataset.id;
    return fetch(`${URL_BASE}/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(collecterDonnees(tr)),
    })
    .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) {
            const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Erreur de validation.');
            throw new Error(msg);
        }
        demarquerModifie(tr);
        return data;
    });
}

function sauvegarderTout() {
    const lignes = document.querySelectorAll('#tableau-body tr[data-dirty="1"]');
    if (lignes.length === 0) {
        afficherFlash('Aucune modification à sauvegarder.', true);
        return;
    }

    const btn = document.getElementById('btn-save-all');
    btn.disabled = true;
    btn.textContent = 'Sauvegarde en cours...';

    Promise.allSettled(Array.from(lignes).map(tr => sauvegarderLigne(tr)))
        .then(resultats => {
            const ok     = resultats.filter(r => r.status === 'fulfilled').length;
            const echecs = resultats.filter(r => r.status === 'rejected');
            if (echecs.length === 0) {
                afficherFlash(`${ok} client(s) sauvegardé(s).`, true);
            } else {
                const premier = echecs[0].reason?.message || 'erreur inconnue';
                afficherFlash(`${ok} sauvegardé(s), ${echecs.length} en erreur (${premier}).`, false);
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Tout sauvegarder';
        });
}
</script>
@endsection
