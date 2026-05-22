@extends('layouts.app')
@section('title', 'Nouvelle intervention')
@section('content')
<a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour au contrat — {{ $contrat->client->nom_societe }}</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:4px">Nouvelle intervention</h1>
<p style="font-size:13px;color:#888;margin-bottom:1.5rem">Ajout manuel sur le contrat {{ $contrat->numero_contrat_vits ?? $contrat->titre }} — {{ $contrat->client->nom_societe }}</p>

<div style="background:#f5f5f5;border:1px solid #e0e0e0;border-radius:8px;padding:10px 14px;font-size:12px;color:#666;margin-bottom:1.5rem;display:flex;align-items:center;gap:8px">
    ℹ️ Les interventions Kizeo sont importées automatiquement. Ce formulaire sert pour les interventions non remontées par Kizeo ou les ajustements administratifs.
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('interventions.store') }}" id="form-intervention">
        @csrf
        <input type="hidden" name="contrat_id" value="{{ $contrat->id }}">
        <input type="hidden" name="duree_minutes" id="duree_minutes_hidden" value="{{ old('duree_minutes', 0) }}">

        {{-- IDENTIFICATION --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_intervention" value="{{ old('date_intervention', now()->toDateString()) }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    @error('date_intervention')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° bon Kizeo</label>
                    <input type="text" name="numero_bon_kizeo" value="{{ old('numero_bon_kizeo') }}" placeholder="9 chiffres (optionnel)" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>
        </div>

        {{-- MOTIF --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Motif</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Motif prédéfini</label>
                    <select name="motif_liste" id="motif-liste" onchange="onMotifChange()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Sélectionner un motif —</option>
                        @foreach($motifs as $motif)
                            <option value="{{ $motif }}" {{ old('motif')==$motif?'selected':'' }}>{{ $motif }}</option>
                        @endforeach
                        <option value="__autre__" {{ old('motif_liste')=='__autre__'?'selected':'' }}>✏ Saisie libre…</option>
                    </select>
                </div>
                <div id="motif-libre-wrap" style="display:{{ old('motif_liste')=='__autre__' ? 'block' : 'none' }}">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Motif libre</label>
                    <input type="text" name="motif" id="motif-libre" value="{{ old('motif') }}" placeholder="Décrire le motif…" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>
            <input type="hidden" name="motif" id="motif-hidden" value="{{ old('motif') }}">
        </div>

        {{-- TYPE + DURÉE --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Type d'intervention</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Type <span style="color:#E8720C">*</span></label>
                    <select name="type" id="type-select" required onchange="onTypeChange()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Sélectionner —</option>
                        <option value="site"           {{ old('type')=='site'?'selected':'' }}>Sur site</option>
                        <option value="distance"       {{ old('type')=='distance'?'selected':'' }}>À distance</option>
                        <option value="flash"          {{ old('type')=='flash'?'selected':'' }}>Flash</option>
                        <option value="administrateur" {{ old('type')=='administrateur'?'selected':'' }}>Administrateur</option>
                    </select>
                    @error('type')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>

                {{-- DURÉE SITE : tranches 1h --}}
                <div id="duree-site" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée <span style="color:#E8720C">*</span></label>
                    <select id="select-site" onchange="updateDuree()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Choisir —</option>
                        @for($h=1;$h<=12;$h++)
                            <option value="{{ $h*60 }}">{{ $h }}h</option>
                        @endfor
                    </select>
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Tranches de 1h</div>
                </div>

                {{-- DURÉE DISTANCE : tranches 20min --}}
                <div id="duree-distance" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée <span style="color:#E8720C">*</span></label>
                    <select id="select-distance" onchange="updateDuree()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Choisir —</option>
                        @for($i=1;$i<=36;$i++)
                            @php $mins = $i*20; $h=floor($mins/60); $m=$mins%60; @endphp
                            <option value="{{ $mins }}">{{ $h>0?$h.'h':'' }}{{ $m>0?$m.'min':'' }}</option>
                        @endfor
                    </select>
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Tranches de 20min</div>
                </div>

                {{-- DURÉE ADMINISTRATEUR : libre --}}
                <div id="duree-admin" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée (minutes) <span style="color:#E8720C">*</span></label>
                    <input type="number" id="input-admin" min="-999" max="999" step="1" placeholder="ex: 60 ou -60 pour crédit" onchange="updateDuree()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Valeur négative = crédit d'heures</div>
                </div>
            </div>

            {{-- FLASH --}}
            <div id="flash-box" style="display:none;background:#f5f5f5;border:1px solid #e0e0e0;border-radius:8px;padding:12px 14px;margin-top:1rem">
                <div style="font-size:12px;font-weight:500;color:#1a1a1a;margin-bottom:10px">⚡ Numéro de flash</div>
                <div style="display:flex;gap:8px;margin-bottom:10px">
                    <div onclick="selectFlash(1)" id="fs1" style="flex:1;border:1px solid #ddd;border-radius:8px;padding:8px;text-align:center;cursor:pointer;background:#fff">
                        <div style="font-size:14px;font-weight:500">1/3</div>
                        <div style="font-size:11px;color:#888;margin-top:2px">0 min débité</div>
                    </div>
                    <div onclick="selectFlash(2)" id="fs2" style="flex:1;border:1px solid #ddd;border-radius:8px;padding:8px;text-align:center;cursor:pointer;background:#fff">
                        <div style="font-size:14px;font-weight:500">2/3</div>
                        <div style="font-size:11px;color:#888;margin-top:2px">0 min débité</div>
                    </div>
                    <div onclick="selectFlash(3)" id="fs3" style="flex:1;border:1px solid #ddd;border-radius:8px;padding:8px;text-align:center;cursor:pointer;background:#fff">
                        <div style="font-size:14px;font-weight:500">3/3</div>
                        <div style="font-size:11px;color:#888;margin-top:2px">20 min débités</div>
                    </div>
                </div>
                <div id="flash-info" style="font-size:12px;color:#666;padding:8px 10px;background:#fff;border-radius:8px;border:1px solid #e0e0e0">
                    Sélectionne le numéro de flash pour cette intervention.
                </div>
                <input type="hidden" name="flash_numero" id="flash-input" value="">
            </div>

            {{-- DÉDUCTIBLE --}}
            <div id="deductible-wrap" style="display:none;margin-top:1rem">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#444">
                    <input type="checkbox" name="deductible" id="deductible-check" value="1" checked style="width:16px;height:16px;accent-color:#E8720C">
                    Déductible du forfait contrat
                </label>
                <div style="font-size:11px;color:#aaa;margin-top:3px;margin-left:24px">Décocher pour une intervention hors contrat (déplacement, pièce, etc.)</div>
            </div>
        </div>

        {{-- NOTES --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Notes internes</div>
            <textarea name="notes" rows="3" placeholder="Commentaire interne (optionnel)…" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <a href="{{ route('contrats.show', $contrat) }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Enregistrer</button>
        </div>
    </form>
</div>

<script>
function onTypeChange() {
    const val = document.getElementById('type-select').value;
    document.getElementById('duree-site').style.display     = val === 'site'           ? 'block' : 'none';
    document.getElementById('duree-distance').style.display = val === 'distance'       ? 'block' : 'none';
    document.getElementById('duree-admin').style.display    = val === 'administrateur' ? 'block' : 'none';
    document.getElementById('flash-box').style.display      = val === 'flash'          ? 'block' : 'none';
    document.getElementById('deductible-wrap').style.display = val && val !== 'flash'  ? 'block' : 'none';
    updateDuree();
}

function updateDuree() {
    const val = document.getElementById('type-select').value;
    let minutes = 0;
    if (val === 'site')           minutes = parseInt(document.getElementById('select-site').value)     || 0;
    if (val === 'distance')       minutes = parseInt(document.getElementById('select-distance').value) || 0;
    if (val === 'administrateur') minutes = parseInt(document.getElementById('input-admin').value)     || 0;
    document.getElementById('duree_minutes_hidden').value = minutes;
}

function selectFlash(n) {
    [1,2,3].forEach(i => {
        const el = document.getElementById('fs'+i);
        el.style.border = i === n ? '1px solid #E8720C' : '1px solid #ddd';
        el.style.background = i === n ? '#FFF3E6' : '#fff';
        el.querySelector('div').style.color = i === n ? '#E8720C' : '#1a1a1a';
    });
    document.getElementById('flash-input').value = n;
    document.getElementById('duree_minutes_hidden').value = n === 3 ? 20 : 0;
    const info = document.getElementById('flash-info');
    if (n === 3) {
        info.style.background = '#FFF3E6'; info.style.borderColor = '#E8720C'; info.style.color = '#854F0B';
        info.textContent = 'Flash 3/3 — 20 minutes seront débitées. Le compteur repart à 1/3.';
    } else {
        info.style.background = '#fff'; info.style.borderColor = '#e0e0e0'; info.style.color = '#666';
        info.textContent = 'Flash '+n+'/3 — aucune minute débitée sur le forfait.';
    }
}

function onMotifChange() {
    const val = document.getElementById('motif-liste').value;
    const wrap = document.getElementById('motif-libre-wrap');
    const hidden = document.getElementById('motif-hidden');
    if (val === '__autre__') {
        wrap.style.display = 'block';
        hidden.value = document.getElementById('motif-libre').value;
    } else {
        wrap.style.display = 'none';
        hidden.value = val;
    }
}

document.getElementById('motif-libre').addEventListener('input', function() {
    document.getElementById('motif-hidden').value = this.value;
});

// Init au chargement
onTypeChange();
onMotifChange();
</script>
@endsection