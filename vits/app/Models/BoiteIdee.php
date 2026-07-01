<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoiteIdee extends Model
{
    use HasFactory;

    protected $table = 'boite_idees';

    protected $fillable = ['user_id', 'prenom', 'type', 'priorite', 'statut', 'commentaire', 'votes', 'admin_note'];

    protected $casts = ['votes' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
}
