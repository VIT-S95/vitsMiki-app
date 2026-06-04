<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = ['nom', 'sujet', 'corps', 'variables', 'actif'];

    protected $casts = [
        'variables' => 'array',
        'actif'     => 'boolean',
    ];
}
