<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class VitsSeeder extends Seeder
{
    public function run(): void
    {
        // Création des rôles  
        $admin      = Role::create(['name' => 'admin']);
        $technique  = Role::create(['name' => 'technique']);
        $coordinatrice = Role::create(['name' => 'coordinatrice']);

        // Stéphane — Administrateur
        $stephane = User::create([
            'name'     => 'Stéphane',
            'email'    => 'sraza@vit-s.com',
            'password' => bcrypt('sraza@vits2026!'),
        ]);
        $stephane->assignRole($admin);

        // Axel — Responsable Technique
        $axel = User::create([
            'name'     => 'Axel',
            'email'    => 'alejeune@vit-s.com',
            'password' => bcrypt('alejeune@vits2026!'),
        ]);
        $axel->assignRole($technique);

        // Filipa — Coordinatrice
        $filipa = User::create([
            'name'     => 'Filipa',
            'email'    => 'fraquel@vit-s.com',
            'password' => bcrypt('fraquel@vits2026!'),
        ]);
        $filipa->assignRole($coordinatrice);
    }
}