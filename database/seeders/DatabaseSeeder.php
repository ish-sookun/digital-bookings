<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'shirley.bourbon@lasentinelle.mu'],
            [
                'firstname' => 'Shirley',
                'lastname' => 'Bourbon',
                'password' => 'password',
                'role' => UserRole::SuperAdmin,
            ],
        );

        User::firstOrCreate(
            ['email' => 'maita.lallman@lasentinelle.mu'],
            [
                'firstname' => 'Maita',
                'lastname' => 'Lallman',
                'password' => 'password',
                'role' => UserRole::Admin,
            ],
        );

        User::firstOrCreate(
            ['email' => 'patricia.caprice@lasentinelle.mu'],
            [
                'firstname' => 'Patricia',
                'lastname' => 'Caprice',
                'password' => 'password',
                'role' => UserRole::Salesperson,
            ],
        );
    }
}
