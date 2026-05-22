@extends('layouts.app')
@section('title', 'Nouvelle intervention')
@section('content')
<a href="{{ route('contrats.show', $contrat) }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour au contrat — {{ $contrat->client->nom_societe }}</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:4px">Nouvelle intervention</h1>
<p style="font-size:13px;color:#888;margin-bottom:1.5rem">Ajout manuel sur le contrat {{ $contrat->numero_contrat_vits ?? $contrat->titre }} — {{ $contrat->client->nom_societe }}</p>

<div style="background:#f5f5f5;border:1px solid #e0e0e0;border-radius:8px;padding:10px 14px;font-size:12px;color:#666;margin-bottom:1.5rem;display:flex;align-items:center;gap:8px">
    ℹ️ Les interventions Kizeo sont importées automatiquement. Ce formulaire sert uniquement pour les interventions non remontées par Kizeo.
</div>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('interventions.store') }}" id="form-intervention">
        @csrf
        <input type="hidden" name="contrat_id" value="{{ $contrat->id }}">

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date de l'intervention <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_intervention" value="{{ old('date_intervention') }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                    @error('date_intervention')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Motif</label>
                    <select name="motif" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Sélectionner un motif —</option>
                        @foreach($motifs as $motif)
                            <option value="{{ $motif }}" {{ old('motif')==$motif?'selected':'' }}>{{ $motif }}</option>
                        @endforeach
                    </select>
                    <div style="font-size:11px;color:#aaa;margin-top:3px">Motifs configurables dans les paramètres</div>
                </div>
            </div>
        </div>

        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Type d'intervention</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Type <span style="color:#E8720C">*</span></label>
                    <select name="type" id="type-select" required onchange="onTypeChange()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Sélectionner —</option>
                        <option value="site" {{ old('type')=='site'?'selected':'' }}>Sur site</option>
                        <option value="distance" {{ old('type')=='distance'?'selected':'' }}>À distance</option>
                        <option value="flash" {{ old('type')=='flash'?'selected':'' }}>Flash</option>
                    </select>
                    @error('type')<div style="color:#dc2626;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                </div>
                <div id="duree-wrap">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée <span style="color:#E8720C">*</span></label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="number" name="heures" id="heures" min="0" max="23" placeholder="0" style="width:70px;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                        <span style="font-size:13px;color:#888">h</span>
                        <input type="number" name="minutes" id="minutes" min="0" max="59" step="5" placeholder="00" style="width:70px;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;text-align:center">
                        <span style="font-size:13px;color:#888">min</span>
                    </div>
                </div>
            </div>

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
                <div id="flash-recalc" style="display:none;background:#FFF3E6;border:1px solid #E8720C;border-radius:8px;padding:8px 12px;font-size:12px;color:#854F0B;margin-top:8px">
                    ⚠️ Une intervention flash existe déjà après cette date — les numéros de flash suivants seront <strong>recalculés automatiquement</strong>.
                </div>
                <input type="hidden" name="flash_numero" id="flash-input" value="">
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <a href="{{ route('contrats.show', $contrat) }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
            <button type="submit" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">✓ Enregistrer l'intervention</button>
        </div>
    </form>
</div>

<script>
function onTypeChange(){
    const val = document.getElementById('type-select').value;
    document.getElementById('duree-wrap').style.display = val === 'flash' ? 'none' : 'block';
    document.getElementById('flash-box').style.display = val === 'flash' ? 'block' : 'none';
}

function selectFlash(n){
    [1,2,3].forEach(i => {
        const el = document.getElementById('fs'+i);
        el.style.border = i === n ? '1px solid #E8720C' : '1px solid #ddd';
        el.style.background = i === n ? '#FFF3E6' : '#fff';
        el.querySelector('div').style.color = i === n ? '#E8720C' : '#1a1a1a';
    });
    document.getElementById('flash-input').value = n;
    const info = document.getElementById('flash-info');
    if(n === 3){
        info.style.background = '#FFF3E6';
        info.style.borderColor = '#E8720C';
        info.style.color = '#854F0B';
        info.textContent = 'Flash 3/3 — 20 minutes seront débitées sur le forfait. Le compteur flash repart à 1/3.';
    } else {
        info.style.background = '#fff';
        info.style.borderColor = '#e0e0e0';
        info.style.color = '#666';
        info.textContent = 'Flash '+n+'/3 — aucune minute débitée sur le forfait.';
    }
}
</script>
@endsection
