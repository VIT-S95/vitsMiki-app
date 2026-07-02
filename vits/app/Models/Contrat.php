<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Contrat extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id','titre','numero_contrat_vits','date_debut','date_fin',
        'duree_mois','duree_periode_mois','heures_par_periode',
        'numero_renouvellement','statut','pdf_contrat',
        'renouvellement_auto','notif_jours_avant'
    ];
    protected $casts = [
        'date_debut'          => 'date',
        'date_fin'            => 'date',
        'renouvellement_auto' => 'boolean',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function interventions() { return $this->hasMany(Intervention::class); }

    public function getJoursRestantsAttribute() {
        if (!$this->date_fin) return null;
        return (int) now()->diffInDays($this->date_fin, false);
    }

    public function getEcheanceLabelAttribute() {
        $jours = $this->jours_restants;
        if ($jours === null) return '—';
        if ($jours < 0) {
            $abs = abs($jours);
            $mois = (int) floor($abs / 30);
            $j = $abs % 30;
            return $mois > 0 ? "{$mois} mois {$j}j dépassé" : "{$abs}j dépassé";
        }
        $mois = (int) floor($jours / 30);
        $j = $jours % 30;
        return $mois > 0 ? "{$mois} mois {$j}j" : "{$jours}j";
    }

    public function getAvancementAttribute() {
        if (!$this->date_debut || !$this->date_fin) return 0;
        $total  = (int) $this->date_debut->diffInDays($this->date_fin);
        $ecoule = (int) $this->date_debut->diffInDays(now());
        if ($total == 0) return 0;
        return min(100, round(($ecoule / $total) * 100));
    }

    public function getAvancementPeriodeAttribute() {
        $periodes = $this->getPeriodes();
        if ($periodes->isEmpty() || !$this->heures_par_periode) return 0;
        $periodeActuelle = $periodes->first(fn($p) => now()->between($p['debut'], $p['fin'])) ?? $periodes->last();
        $minutes = $this->interventions
            ->where('deductible', true)
            ->filter(fn($i) => $i->date_intervention >= $periodeActuelle['debut'] && $i->date_intervention <= $periodeActuelle['fin'])
            ->sum('duree_minutes');
        if ($periodeActuelle['numero'] === 1) {
            $minutes += $this->interventions
                ->where('deductible', true)
                ->filter(fn($i) => $i->date_intervention < $this->date_debut)
                ->sum('duree_minutes');
        }
        return round(($minutes / ($this->heures_par_periode * 60)) * 100);
    }

    public function getPeriodes() {
        if (!$this->date_debut) return collect();
        $periodes = collect();
        $debut    = $this->date_debut->copy();
        $dateFin  = $this->date_fin ?? now();
        $numero   = 1;
        while ($debut->lt($dateFin)) {
            $fin = $debut->copy()->addMonths((int)$this->duree_periode_mois)->subDay();
            $periodes->push([
                'numero' => $numero,
                'debut'  => $debut->copy(),
                'fin'    => $fin->copy(),
            ]);
            $debut->addMonths((int)$this->duree_periode_mois);
            $numero++;
            if ($numero > 100) break; // sécurité boucle infinie
        }
        return $periodes;
    }
}
