@extends('layouts.app')

@section('title', 'Calcul prorata')

@section('content')
<div style="max-width:700px">

    <h2 style="font-size:1.3rem;font-weight:700;color:#1a1a1a;margin-bottom:0.25rem">
        Calculateur Prorata
    </h2>
    <p style="font-size:12px;color:#aaa;margin-bottom:1.5rem">Calcul du montant au prorata entre deux dates</p>

    {{-- Paramètres --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem">
        <div style="font-size:11px;font-weight:600;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem">Paramètres</div>
        <div style="display:flex;gap:2rem;flex-wrap:wrap">
            <div>
                <label style="display:block;font-size:12px;color:#666;margin-bottom:4px">Date de début</label>
                <input type="date" id="date_debut" style="padding:7px 10px;border:1px solid #ddd;border-radius:7px;font-size:13px;width:170px">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:#666;margin-bottom:4px">Date anniversaire</label>
                <input type="date" id="date_fin" style="padding:7px 10px;border:1px solid #ddd;border-radius:7px;font-size:13px;width:170px">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:#666;margin-bottom:4px">Montant annuel HT (€)</label>
                <input type="number" id="montant_annuel" step="0.01" min="0" placeholder="0.00"
                       style="padding:7px 10px;border:1px solid #ddd;border-radius:7px;font-size:13px;width:160px">
            </div>
        </div>
    </div>

    {{-- Résultats --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden">
        <div style="font-size:11px;font-weight:600;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;padding:1rem 1.5rem 0.75rem;border-bottom:1px solid #f0f0f0">Résultats</div>
        <div style="padding:1rem 1.5rem">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f5f5f5">
                <span style="font-size:13px;color:#666">Nombre de jours</span>
                <span id="res_jours" style="font-size:13px;font-weight:500;color:#1a1a1a">—</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f5f5f5">
                <span style="font-size:13px;color:#666">Décomposition</span>
                <span id="res_decomposition" style="font-size:13px;font-weight:500;color:#1a1a1a">—</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f5f5f5">
                <span style="font-size:13px;color:#666">Base de calcul</span>
                <span id="res_base" style="font-size:13px;font-weight:500;color:#1a1a1a">—</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;margin-top:10px;background:#fff8f3;border-radius:8px;border:1px solid #fde8d2">
                <span style="font-size:13px;font-weight:600;color:#E8720C">Montant prorata HT</span>
                <span id="res_montant" style="font-size:20px;font-weight:700;color:#E8720C">—</span>
            </div>
        </div>
    </div>

</div>

<script>
    ['date_debut', 'date_fin', 'montant_annuel'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', calculer);
    });

    function estBissextile(annee) {
        return (annee % 4 === 0 && annee % 100 !== 0) || (annee % 400 === 0);
    }

    function decomposer(debut, fin) {
        var mois = 0;
        var d = new Date(debut);
        var f = new Date(fin);
        while (true) {
            var next = new Date(d);
            next.setMonth(next.getMonth() + 1);
            if (next > f) break;
            mois++;
            d = next;
        }
        var joursRestants = Math.round((f - d) / (1000 * 60 * 60 * 24));
        return { mois: mois, jours: joursRestants };
    }

    function calculer() {
        var dateDebut = document.getElementById('date_debut').value;
        var dateFin   = document.getElementById('date_fin').value;
        var montant   = parseFloat(document.getElementById('montant_annuel').value);
        var ids = ['res_jours', 'res_decomposition', 'res_base', 'res_montant'];

        if (!dateDebut || !dateFin || isNaN(montant)) {
            ids.forEach(function(id) { document.getElementById(id).textContent = '—'; });
            return;
        }

        var d1 = new Date(dateDebut);
        var d2 = new Date(dateFin);

        if (d2 <= d1) {
            ids.forEach(function(id) { document.getElementById(id).textContent = '—'; });
            return;
        }

        var nbJours = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
        var base    = estBissextile(d1.getFullYear()) ? 366 : 365;
        var prorata = (montant * nbJours) / base;
        var dec     = decomposer(d1, d2);

        document.getElementById('res_jours').textContent = nbJours + ' jour(s)';
        document.getElementById('res_decomposition').textContent =
            dec.mois > 0 ? dec.mois + ' mois ' + dec.jours + ' jour(s)' : dec.jours + ' jour(s)';
        document.getElementById('res_base').textContent =
            base + ' jours (' + (base === 366 ? 'année bissextile' : 'année normale') + ')';
        document.getElementById('res_montant').textContent = prorata.toFixed(2) + ' €';
    }
</script>
@endsection
