@extends('layouts.app')
@section('title', 'Modifier intervention')
@section('content')
@if($intervention->contrat_id)
<a href="{{ route('contrats.show', $intervention->contrat_id) }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour au contrat — {{ $intervention->contrat?->client?->nom_societe }}</a>
@else
<span style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;margin-bottom:1.25rem">Intervention hors contrat — {{ $intervention->client_nom ?? '—' }}</span>
@endif
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:4px">Modifier l'intervention</h1>
<p style="font-size:13px;color:#888;margin-bottom:1.5rem">Bon n° {{ $intervention->numero_bon_kizeo ?? $intervention->id }} — {{ $intervention->date_intervention->format('d/m/Y') }}</p>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('interventions.update', $intervention) }}" id="form-intervention">
        @csrf
        @method('PUT')
        <input type="hidden" name="duree_minutes" id="duree_minutes_hidden" value="{{ $intervention->duree_minutes }}">

        {{-- IDENTIFICATION --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_intervention" value="{{ old('date_intervention', $intervention->date_intervention->format('Y-m-d')) }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° bon Kizeo</label>
                    <input type="text" name="numero_bon_kizeo" value="{{ old('numero_bon_kizeo', $intervention->numero_bon_kizeo) }}" placeholder="9 chiffres (optionnel)" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>
        </div>

        {{-- MOTIF --}}
        @php
            $motifValeur = old('motif', $intervention->motif);
            $motifDansListe = in_array($motifValeur, $motifs);
            $motifListeVal = $motifDansListe ? $motifValeur : ($motifValeur ? '__autre__' : '');
        @endphp
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Motif</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Motif prédéfini</label>
                    <select name="motif_liste" id="motif-liste" onchange="onMotifChange()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Sélectionner —</option>
                        @foreach($motifs as $motif)
                            <option value="{{ $motif }}" {{ $motifListeVal==$motif?'selected':'' }}>{{ $motif }}</option>
                        @endforeach
                        <option value="__autre__" {{ $motifListeVal=='__autre__'?'selected':'' }}>✏ Saisie libre…</option>
                    </select>
                </div>
                <div id="motif-libre-wrap" style="display:{{ $motifListeVal=='__autre__' ? 'block' : 'none' }}">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Motif libre</label>
                    <input type="text" name="motif_libre" id="motif-libre" value="{{ !$motifDansListe ? $motifValeur : '' }}" placeholder="Décrire le motif…" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>
            <input type="hidden" name="motif" id="motif-hidden" value="{{ $motifValeur }}">
        </div>

        {{-- TYPE + DURÉE --}}
        @php $typeVal = old('type', $intervention->type); @endphp
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Type d'intervention</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Type <span style="color:#E8720C">*</span></label>
                    <select name="type" id="type-select" required onchange="onTypeChange()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="site"           {{ $typeVal=='site'?'selected':'' }}>Sur site</option>
                        <option value="distance"       {{ $typeVal=='distance'?'selected':'' }}>À distance</option>
                        <option value="flash"          {{ $typeVal=='flash'?'selected':'' }}>Flash</option>
                        <option value="administrateur" {{ $typeVal=='administrateur'?'selected':'' }}>Administrateur</option>
                    </select>
                </div>

                <div id="duree-site" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée <span style="color:#E8720C">*</span></label>
                    <select id="select-site" onchange="updateDuree()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        @for($h=1;$h<=12;$h++)
                            <option value="{{ $h*60 }}" {{ $intervention->duree_minutes==$h*60?'selected':'' }}>{{ $h }}h</option>
                        @endfor
                    </select>
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Tranches de 1h</div>
                </div>

                <div id="duree-distance" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée <span style="color:#E8720C">*</span></label>
                    <select id="select-distance" onchange="updateDuree()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        @for($i=1;$i<=36;$i++)
                            @php $mins=$i*20; $h=floor($mins/60); $m=$mins%60; @endphp
                            <option value="{{ $mins }}" {{ $intervention->duree_minutes==$mins?'selected':'' }}>{{ $h>0?$h.'h':'' }}{{ $m>0?$m.'min':'' }}</option>
                        @endfor
                    </select>
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Tranches de 20min</div>
                </div>

                <div id="duree-admin" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée (minutes)</label>
                    <input type="number" id="input-admin" min="-999" max="999" step="1" value="{{ $intervention->duree_minutes }}" onchange="updateDuree()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Valeur négative = crédit d'heures</div>
                </div>
            </div>

            <div id="flash-box" style="display:none;background:#f5f5f5;border:1px solid #e0e0e0;border-radius:8px;padding:12px 14px;margin-top:1rem">
                <div style="font-size:12px;font-weight:500;color:#1a1a1a;margin-bottom:10px">⚡ Numéro de flash</div>
                <div style="display:flex;gap:8px;margin-bottom:10px">
                    @foreach([1,2,3] as $fn)
                    <div onclick="selectFlash({{ $fn }})" id="fs{{ $fn }}" style="flex:1;border:{{ $intervention->flash_numero==$fn?'1px solid #E8720C':'1px solid #ddd' }};background:{{ $intervention->flash_numero==$fn?'#FFF3E6':'#fff' }};border-radius:8px;padding:8px;text-align:center;cursor:pointer">
                        <div style="font-size:14px;font-weight:500;color:{{ $intervention->flash_numero==$fn?'#E8720C':'#1a1a1a' }}">{{ $fn }}/3</div>
                        <div style="font-size:11px;color:#888;margin-top:2px">{{ $fn==3?'20 min débités':'0 min débité' }}</div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="flash_numero" id="flash-input" value="{{ $intervention->flash_numero }}">
            </div>

            <div id="deductible-wrap" style="display:{{ $typeVal!='flash'?'block':'none' }};margin-top:1rem">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#444">
                    <input type="checkbox" name="deductible" id="deductible-check" value="1" {{ $intervention->deductible?'checked':'' }} style="width:16px;height:16px;accent-color:#E8720C">
                    Déductible du forfait contrat
                </label>
            </div>
        </div>

        {{-- NOTES --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Notes internes</div>
            <textarea name="notes" rows="3" placeholder="Commentaire interne…" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical">{{ old('notes', $intervention->notes) }}</textarea>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <form method="POST" action="{{ route('interventions.destroy', $intervention) }}" onsubmit="return confirm('Supprimer cette intervention ?')">
                @csrf @method('DELETE')
                <button type="submit" style="padding:8px 14px;font-size:12px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:8px;cursor:pointer">🗑 Supprimer</button>
            </form>
            <div style="display:flex;gap:8px">
                @if($intervention->contrat_id)
                <a href="{{ route('contrats.show', $intervention->contrat_id) }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
                @else
                <a href="{{ route('interventions.index') }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
                @endif
                <button type="submit" form="form-intervention" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Enregistrer</button>
            </div>
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
        el.style.border = i===n ? '1px solid #E8720C' : '1px solid #ddd';
        el.style.background = i===n ? '#FFF3E6' : '#fff';
        el.querySelector('div').style.color = i===n ? '#E8720C' : '#1a1a1a';
    });
    document.getElementById('flash-input').value = n;
    document.getElementById('duree_minutes_hidden').value = n===3 ? 20 : 0;
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

onTypeChange();
</script>
@endsection