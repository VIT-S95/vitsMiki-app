<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Intervention extends Model
{
    use HasFactory;
    protected $fillable = [
        'contrat_id','date_intervention','heure_intervention','technicien','numero_bon_kizeo',
        'type','flash_numero','flash_consomme','duree_minutes',
        'motif','notes','statut','type_tri','source_kizeo','deductible'
    ];
    protected $casts = [
        'date_intervention' => 'date',
        'flash_consomme'    => 'boolean',
        'source_kizeo'      => 'boolean',
        'deductible'        => 'boolean',
    ];
    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }
    public function getDureeFormateeAttribute()
    {
        if ($this->type === 'flash' && $this->flash_numero < 3) return '—';
        $h = floor($this->duree_minutes / 60);
        $m = $this->duree_minutes % 60;
        if ($h > 0 && $m > 0) return "{$h}h {$m}min";
        if ($h > 0) return "{$h}h";
        return "{$m}min";
    }
    public static function recalculerFlash($contrat_id, $date_insertion)
    {
        $contrat = \App\Models\Contrat::find($contrat_id);
        $dateDebut = $contrat?->date_debut;
        $interventions = self::where('contrat_id', $contrat_id)
            ->where('type', 'flash')
            ->when($dateDebut, function($q) use ($dateDebut) {
                $q->where('date_intervention', '>=', $dateDebut);
            })
            ->orderBy('date_intervention')
            ->orderBy('id')
            ->get();
        $compteur = 1;
        foreach ($interventions as $intervention) {
            $intervention->flash_numero = $compteur;
            $intervention->flash_consomme = ($compteur == 3);
            $intervention->duree_minutes = ($compteur == 3) ? 20 : 0;
            $intervention->save();
            $compteur = ($compteur == 3) ? 1 : $compteur + 1;
        }
    }
}
