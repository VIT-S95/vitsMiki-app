<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PeriodeAjustement extends Model
{
    protected $fillable = ['contrat_id','periode_numero','periode_fin','solde_minutes','action'];
    protected $casts = ['periode_fin' => 'date'];
    public function contrat() { return $this->belongsTo(Contrat::class); }
}
