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
        $this->call(KizeoFieldMappingsSeeder::class);

        User::updateOrCreate(
            ['email' => 'sraza@vit-s.com'],
            [
                'name'     => 'Stéphane',
                'password' => Hash::make('changeme'),
                'is_admin' => true,
                'themes'   => null,
            ]
        );
    }
}
