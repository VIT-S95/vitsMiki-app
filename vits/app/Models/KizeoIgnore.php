<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KizeoIgnore extends Model
{
    protected $fillable = ['kizeo_id', 'form_id', 'raison'];
}
