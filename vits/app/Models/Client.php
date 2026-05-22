<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    protected $fillable = ['nom_societe','nom_signataire','email_signataire','numero_client_kizeo','numero_contrat_vits','statut'];

    public function contrats() { return $this->hasMany(Contrat::class); }

    public function getInitialesAttribute() {
        $words = explode(' ', $this->nom_societe);
        return strtoupper(substr($words[0],0,1).(isset($words[1]) ? substr($words[1],0,1) : substr($words[0],1,1)));
    }
}
