<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'themes', 'is_admin', 'client_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'themes'            => 'array',
            'is_admin'          => 'boolean',
        ];
    }

    public function client() { return $this->belongsTo(Client::class); }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isPortailClient(): bool
    {
        return !is_null($this->client_id);
    }

    public function hasTheme(string $theme): bool
    {
        if ($this->isAdmin()) return true;

        return in_array($theme, $this->themes ?? [], true);
    }
}
