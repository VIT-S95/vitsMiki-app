@extends('layouts.app')

@section('title', 'Calcul Bitdefender')

@section('content')
<div style="max-width:900px">

    <h2 style="font-size:1.3rem;font-weight:700;color:#1a1a1a;margin-bottom:0.25rem">
        Calculateur Devis — Bitdefender GravityZone BSE
    </h2>
    <p style="font-size:12px;color:#aaa;margin-bottom:1.5rem">Calage sur année civile — Calcul prorata automatique</p>

    {{-- Paramètres --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem">
        <div style="font-size:11px;font-weight:600;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:1rem">Paramètres client</div>
        <div style="display:flex;gap:2rem;flex-wrap:wrap">
            <div>
                <label style="display:block;font-size:12px;color:#666;margin-bottom:4px">Date anniversaire client</label>
                <input type="date" id="dateAnniv" style="padding:7px 10px;border:1px solid #ddd;border-radius:7px;font-size:13px;width:170px">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:#666;margin-bottom:4px">Nombre de postes</label>
                <input type="number" id="nbPostes" min="1" value="27" style="padding:7px 10px;border:1px solid #ddd;border-radius:7px;font-size:13px;width:120px">
            </div>
            <div>
                <label style="display:block;font-size:12px;color:#666;margin-bottom:4px">Prix annuel / poste (€ HT)</label>
                <input type="number" id="prixAnnuel" min="0" step="0.01" value="77.60" style="padding:7px 10px;border:1px solid #ddd;border-radius:7px;font-size:13px;width:140px">
            </div>
        </div>
    </div>

    {{-- Tableau résultat --}}
    <div style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden">
        <table id="tableDevis" style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f8f8f8;border-bottom:1px solid #e0e0e0">
                    <th style="padding:10px 12px;text-align:center;width:40px;color:#888;font-weight:600">N°</th>
                    <th style="padding:10px 12px;text-align:left;color:#888;font-weight:600">Désignation</th>
                    <th style="padding:10px 12px;text-align:center;color:#888;font-weight:600;width:170px">Période de couverture</th>
                    <th style="padding:10px 12px;text-align:center;color:#888;font-weight:600;width:100px">Jours / Base</th>
                    <th style="padding:10px 12px;text-align:center;color:#888;font-weight:600;width:60px">Qté</th>
                    <th style="padding:10px 12px;text-align:right;color:#888;font-weight:600;width:90px">P.U. HT</th>
                    <th style="padding:10px 12px;text-align:right;color:#888;font-weight:600;width:100px">Total HT</th>
                </tr>
            </thead>
            <tbody id="lignesDevis">
                <tr><td colspan="7" style="padding:2rem;text-align:center;color:#ccc;font-size:13px">Renseignez les paramètres pour générer le devis</td></tr>
            </tbody>
            <tfoot id="totalDevis" style="display:none">
                <tr style="background:#fff8f3;border-top:2px solid #E8720C">
                    <td colspan="6" style="padding:10px 12px;text-align:right;font-weight:700;color:#1a1a1a">TOTAL GÉNÉRAL HT</td>
                    <td id="totalHT" style="padding:10px 12px;text-align:right;font-weight:700;color:#E8720C;font-size:14px"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <p style="font-size:11px;color:#bbb;margin-top:0.75rem">
        P.U. HT = prix prorata pour 1 poste. Total HT = P.U. × nombre de postes. Prorata calculé au nombre de jours réels (base 365 ou 366 j selon année bissextile).
    </p>
</div>

<script>
const DESIGNATION = "BitDefender GravityZone Business Security Enterprise\nAbonnement annuel, tacitement renouvelable, préavis 2 mois.";

function estBissextile(annee) {
    return (annee % 4 === 0 && annee % 100 !== 0) || (annee % 400 === 0);
}

function baseAnnee(annee) {
    return estBissextile(annee) ? 366 : 365;
}

function diffJours(d1, d2) {
    return Math.round((d2 - d1) / 86400000) + 1;
}

function formatDate(d) {
    return d.toLocaleDateString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric'});
}

function formatEur(v) {
    return v.toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' €';
}

function calculer() {
    const dateVal  = document.getElementById('dateAnniv').value;
    const nbPostes = parseFloat(document.getElementById('nbPostes').value);
    const prixAn   = parseFloat(document.getElementById('prixAnnuel').value);
    const tbody    = document.getElementById('lignesDevis');
    const tfoot    = document.getElementById('totalDevis');

    if (!dateVal || !nbPostes || !prixAn) {
        tbody.innerHTML = '<tr><td colspan="7" style="padding:2rem;text-align:center;color:#ccc;font-size:13px">Renseignez les paramètres pour générer le devis</td></tr>';
        tfoot.style.display = 'none';
        return;
    }

    const anniv = new Date(dateVal);
    const annee = anniv.getFullYear();

    // Ligne 1 : date anniversaire → 31/12/annee
    const fin1   = new Date(annee, 11, 31);
    const jours1 = diffJours(anniv, fin1);
    const base1  = baseAnnee(annee);
    const pu1    = (prixAn / base1) * jours1;
    const total1 = pu1 * nbPostes;

    // Ligne 2 : 01/01/annee+1 → veille de la date anniversaire l'année suivante
    const debut2 = new Date(annee + 1, 0, 1);
    const fin2   = new Date(annee + 1, anniv.getMonth(), anniv.getDate() - 1);
    const jours2 = diffJours(debut2, fin2);
    const base2  = baseAnnee(annee + 1);
    const pu2    = (prixAn / base2) * jours2;
    const total2 = pu2 * nbPostes;

    const totalGeneral = total1 + total2;

    const rowStyle = 'border-bottom:1px solid #f0f0f0';
    const noteStyle = 'font-size:11px;color:#aaa;margin-top:4px';
    const desig1note = annee === new Date().getFullYear()
        ? 'Facturation immédiate à la signature'
        : `Facturation à la signature`;

    tbody.innerHTML = `
        <tr style="${rowStyle}">
            <td style="padding:12px;text-align:center;color:#888">1</td>
            <td style="padding:12px">
                <div style="font-weight:500;color:#1a1a1a">BitDefender GravityZone Business Security Enterprise</div>
                <div style="${noteStyle}">Abonnement annuel, tacitement renouvelable, préavis 2 mois.</div>
                <div style="${noteStyle}">${desig1note}</div>
            </td>
            <td style="padding:12px;text-align:center;color:#555">${formatDate(anniv)} au ${formatDate(fin1)}</td>
            <td style="padding:12px;text-align:center;color:#555">${jours1} j / ${base1} j</td>
            <td style="padding:12px;text-align:center;color:#555">${nbPostes}</td>
            <td style="padding:12px;text-align:right;color:#555">${formatEur(pu1)}</td>
            <td style="padding:12px;text-align:right;font-weight:500;color:#1a1a1a">${formatEur(total1)}</td>
        </tr>
        <tr style="${rowStyle}">
            <td style="padding:12px;text-align:center;color:#888">2</td>
            <td style="padding:12px">
                <div style="font-weight:500;color:#1a1a1a">BitDefender GravityZone Business Security Enterprise</div>
                <div style="${noteStyle}">Abonnement annuel, tacitement renouvelable, préavis 2 mois.</div>
                <div style="${noteStyle}">Facturation différée au 01/01/${annee + 1}</div>
            </td>
            <td style="padding:12px;text-align:center;color:#555">${formatDate(debut2)} au ${formatDate(fin2)}</td>
            <td style="padding:12px;text-align:center;color:#555">${jours2} j / ${base2} j</td>
            <td style="padding:12px;text-align:center;color:#555">${nbPostes}</td>
            <td style="padding:12px;text-align:right;color:#555">${formatEur(pu2)}</td>
            <td style="padding:12px;text-align:right;font-weight:500;color:#1a1a1a">${formatEur(total2)}</td>
        </tr>`;

    document.getElementById('totalHT').textContent = formatEur(totalGeneral);
    tfoot.style.display = '';
}

document.getElementById('dateAnniv').addEventListener('input', calculer);
document.getElementById('nbPostes').addEventListener('input', calculer);
document.getElementById('prixAnnuel').addEventListener('input', calculer);

// Pré-remplir avec la date du jour
document.getElementById('dateAnniv').value = new Date().toISOString().split('T')[0];
calculer();
</script>
@endsection
