@extends('layouts.app')
@section('title', 'Modifier intervention')
@section('content')
<a href="{{ route('interventions.index') }}" style="display:flex;align-items:center;gap:6px;font-size:13px;color:#888;text-decoration:none;margin-bottom:1.25rem">← Retour aux interventions</a>
<h1 style="font-size:18px;font-weight:500;color:#1a1a1a;margin-bottom:4px">Modifier l'intervention</h1>
<p style="font-size:13px;color:#888;margin-bottom:1.5rem">Bon n° {{ $intervention->numero_bon_kizeo ?? $intervention->id }} — {{ $intervention->date_intervention->format('d/m/Y') }}</p>

<div style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:1.5rem">
    <form method="POST" action="{{ route('interventions.update', $intervention) }}" id="form-intervention">
        @csrf
        @method('PUT')

        {{-- IDENTIFICATION --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Identification</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Date <span style="color:#E8720C">*</span></label>
                    <input type="date" name="date_intervention" value="{{ old('date_intervention', $intervention->date_intervention->format('Y-m-d')) }}" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Heure</label>
                    <input type="time" name="heure_intervention" value="{{ old('heure_intervention', $intervention->heure_intervention) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Technicien</label>
                    <input type="text" name="technicien" value="{{ old('technicien', $intervention->technicien) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Client</label>
                    @php $clientNomVal = old('client_nom', $intervention->client_nom); @endphp
                    <select name="client_nom" id="client-select" onchange="onClientChange()" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Sélectionner —</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->nom_societe }}" {{ $clientNomVal === $client->nom_societe ? 'selected' : '' }}>{{ $client->nom_societe }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Contrat</label>
                    <select name="contrat_id" id="contrat-select" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        <option value="">— Hors contrat —</option>
                        @foreach($contratsActifs as $c)
                        <option value="{{ $c->id }}" {{ (old('contrat_id', $intervention->contrat_id) == $c->id) ? 'selected' : '' }}>
                            {{ $c->client->nom_societe }} — {{ $c->numero_contrat_vits ?? 'N/A' }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- TYPE + DURÉE --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Type d'intervention</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Type <span style="color:#E8720C">*</span></label>
                    <select name="type" id="type_intervention" onchange="onTypeChange()" required style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                        @php $typeVal = old('type', $intervention->type); @endphp
                        <option value="site"       {{ $typeVal=='site'?'selected':'' }}>Sur site</option>
                        <option value="distance"   {{ $typeVal=='distance'?'selected':'' }}>À distance</option>
                        <option value="flash"      {{ $typeVal=='flash'?'selected':'' }}>Flash</option>
                        <option value="ajustement" {{ $typeVal=='ajustement'?'selected':'' }}>Ajustement</option>
                    </select>
                </div>

                {{-- DURÉE SITE/DISTANCE --}}
                <div id="bloc_duree_standard">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée <span style="color:#E8720C">*</span></label>
                    <div style="display:flex;align-items:center;gap:8px">
                        <button type="button" onclick="changerDuree(-1)" style="width:36px;height:36px;border:1px solid #ddd;border-radius:8px;font-size:18px;cursor:pointer;background:#f5f5f5">−</button>
                        <div id="duree_affichage" style="min-width:80px;text-align:center;font-size:15px;font-weight:500;color:#1a1a1a">—</div>
                        <button type="button" onclick="changerDuree(+1)" style="width:36px;height:36px;border:1px solid #ddd;border-radius:8px;font-size:18px;cursor:pointer;background:#f5f5f5">+</button>
                    </div>
                </div>

                {{-- DURÉE FLASH --}}
                <div id="bloc_duree_flash" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Flash</label>
                    <div style="display:flex;gap:8px">
                        @php $flashNum = old('flash_numero', $intervention->flash_numero ?? 1); @endphp
                        <button type="button" onclick="setFlash(1)" id="flash_1" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">1/3</button>
                        <button type="button" onclick="setFlash(2)" id="flash_2" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">2/3</button>
                        <button type="button" onclick="setFlash(3)" id="flash_3" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:8px;font-size:13px;cursor:pointer">3/3 — 20min</button>
                    </div>
                </div>

                {{-- DURÉE AJUSTEMENT --}}
                <div id="bloc_duree_ajustement" style="display:none">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Durée (minutes)</label>
                    <input type="number" id="duree_ajustement_input" onchange="document.getElementById('duree_minutes_hidden').value=this.value" min="-999" max="999" step="1" value="{{ old('duree_minutes', $intervention->duree_minutes) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>

            {{-- Champ hidden qui sera soumis --}}
            <input type="hidden" name="duree_minutes" id="duree_minutes_hidden" value="{{ old('duree_minutes', $intervention->duree_minutes) }}">
            <input type="hidden" name="flash_numero" id="flash_numero_hidden" value="{{ old('flash_numero', $intervention->flash_numero ?? 1) }}">

            <div style="display:flex;gap:1.5rem;margin-top:1rem">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#444">
                    <input type="checkbox" name="deductible" value="1" {{ old('deductible', $intervention->deductible) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#E8720C">
                    Déductible du forfait contrat
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:#444">
                    <input type="checkbox" name="hors_heure_ouvree" value="1" {{ old('hors_heure_ouvree', $intervention->hors_heure_ouvree) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#E8720C">
                    Hors heure ouvrée
                </label>
            </div>
        </div>

        <script>
        const TRANCHE_SITE     = 60;
        const TRANCHE_DISTANCE = 20;
        let dureeActuelle = {{ old('duree_minutes', $intervention->duree_minutes) }};
        let flashActuel   = {{ old('flash_numero', $intervention->flash_numero ?? 1) }};

        function formatDuree(min) {
            if (min === 0) return '0min';
            const h = Math.floor(Math.abs(min) / 60);
            const m = Math.abs(min) % 60;
            const sign = min < 0 ? '-' : '';
            if (h > 0 && m > 0) return sign + h + 'h ' + m + 'min';
            if (h > 0) return sign + h + 'h';
            return sign + m + 'min';
        }

        function getType() {
            return document.getElementById('type_intervention').value;
        }

        function changerDuree(delta) {
            const type = getType();
            const tranche = type === 'site' ? TRANCHE_SITE : TRANCHE_DISTANCE;
            dureeActuelle += delta * tranche;
            if (dureeActuelle < 0) dureeActuelle = 0;
            document.getElementById('duree_minutes_hidden').value = dureeActuelle;
            document.getElementById('duree_affichage').textContent = formatDuree(dureeActuelle);
        }

        function setFlash(num) {
            flashActuel = num;
            dureeActuelle = num === 3 ? 20 : 0;
            document.getElementById('duree_minutes_hidden').value = dureeActuelle;
            document.getElementById('flash_numero_hidden').value = flashActuel;
            [1,2,3].forEach(n => {
                const btn = document.getElementById('flash_' + n);
                btn.style.background = n === num ? '#E8720C' : '#f5f5f5';
                btn.style.color = n === num ? '#fff' : '#1a1a1a';
                btn.style.borderColor = n === num ? '#E8720C' : '#ddd';
            });
        }

        function onTypeChange() {
            const type = getType();
            document.getElementById('bloc_duree_standard').style.display   = (type === 'site' || type === 'distance') ? 'block' : 'none';
            document.getElementById('bloc_duree_flash').style.display      = type === 'flash' ? 'block' : 'none';
            document.getElementById('bloc_duree_ajustement').style.display = type === 'ajustement' ? 'block' : 'none';
            if (type === 'site' || type === 'distance') {
                document.getElementById('duree_affichage').textContent = formatDuree(dureeActuelle);
            }
            if (type === 'flash') setFlash(flashActuel);
        }

        // Init au chargement
        onTypeChange();
        if (getType() === 'flash') setFlash(flashActuel);
        </script>

        {{-- DEMANDE --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Demande</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Donneur d'ordre</label>
                    <input type="text" name="donneur_ordre" value="{{ old('donneur_ordre', $intervention->donneur_ordre) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° ticket</label>
                    <input type="text" name="n_ticket" value="{{ old('n_ticket', $intervention->n_ticket) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° devis</label>
                    <input type="text" name="n_devis" value="{{ old('n_devis', $intervention->n_devis) }}" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px">
                </div>
            </div>
        </div>

        {{-- COMMENTAIRES --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Commentaires</div>
            <textarea name="commentaires" rows="3" placeholder="Commentaire…" style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:13px;resize:vertical">{{ old('commentaires', $intervention->commentaires) }}</textarea>
        </div>

        {{-- LECTURE SEULE --}}
        <div style="margin-bottom:1.5rem">
            <div style="font-size:12px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem;padding-bottom:6px;border-bottom:1px solid #f0f0f0">Informations Kizeo (lecture seule)</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">N° bon Kizeo</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888">{{ $intervention->numero_bon_kizeo ?? '—' }}</div>
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Statut</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888">{{ $intervention->statut ?? '—' }}</div>
                </div>
                <div>
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Origine</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888">{{ $intervention->source_kizeo ? 'Kizeo' : 'Manuel' }}</div>
                </div>
                <div style="grid-column:1/-1">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Demande annexe</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888;white-space:pre-wrap">{{ $intervention->demande_annexe ?? '—' }}</div>
                </div>
                <div style="grid-column:1/-1">
                    <label style="font-size:12px;color:#666;display:block;margin-bottom:5px">Pièces détachées</label>
                    <div style="width:100%;padding:8px 10px;border:1px solid #eee;border-radius:8px;font-size:13px;background:#f5f5f5;color:#888;white-space:pre-wrap">{{ $intervention->pieces_detachees ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:1.25rem;border-top:1px solid #f0f0f0">
            <button type="submit" form="form-delete-intervention"
                    onclick="return confirm('Supprimer cette intervention ?')"
                    style="padding:8px 14px;font-size:12px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:8px;cursor:pointer">&#128465; Supprimer</button>
            <div style="display:flex;gap:8px">
                <a href="{{ route('interventions.index') }}" style="padding:8px 16px;font-size:13px;border:1px solid #ddd;border-radius:8px;color:#666;text-decoration:none">Annuler</a>
                <button type="submit" form="form-intervention" style="padding:8px 20px;font-size:13px;font-weight:500;background:#E8720C;color:#fff;border:none;border-radius:8px;cursor:pointer">&#10003; Enregistrer</button>
            </div>
        </div>
    </form>
</div>

{{-- Formulaire de suppression séparé (hors du form d'édition pour éviter les forms imbriqués) --}}
<form id="form-delete-intervention" method="POST" action="{{ route('interventions.destroy', $intervention) }}">
    @csrf @method('DELETE')
</form>

<script>
const baseContratsParClientUrl = "{{ route('interventions.contrats-par-client', '__CLIENT__') }}";

function onClientChange() {
    const clientNom = document.getElementById('client-select').value;
    const contratSelect = document.getElementById('contrat-select');
    contratSelect.innerHTML = '<option value="">— Hors contrat —</option>';
    if (!clientNom) return;
    fetch(baseContratsParClientUrl.replace('__CLIENT__', encodeURIComponent(clientNom)))
        .then(r => r.json())
        .then(contrats => {
            contrats.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.numero_contrat_vits || 'N/A';
                contratSelect.appendChild(opt);
            });
        });
}
</script>
@endsection
