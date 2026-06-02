<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(VitsSeeder::class);

        User::updateOrCreate(
            ['email' => 'stephane@vit-s.fr'],
            [
                'name'     => 'Stéphane',
                'password' => Hash::make('changeme'),
                'is_admin' => true,
                'themes'   => null,
            ]
        );
    }
}
