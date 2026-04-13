<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'firstname' => 'Ish',
                'lastname' => 'Sookun',
                'email' => 'ish.sookun@lasentinelle.mu',
                'role' => UserRole::SuperAdmin,
            ],
            [
                'firstname' => 'Shirley',
                'lastname' => 'Bourbon',
                'email' => 'shirley.bourbon@lasentinelle.mu',
                'role' => UserRole::SuperAdmin,
            ],
            [
                'firstname' => 'Maïta',
                'lastname' => 'Lallman',
                'email' => 'maita.lallman@lasentinelle.mu',
                'role' => UserRole::Admin,
            ],
            [
                'firstname' => 'Patricia',
                'lastname' => 'Caprice',
                'email' => 'patricia.caprice@lasentinelle.mu',
                'role' => UserRole::Salesperson,
            ],
            [
                'firstname' => 'Jenna',
                'lastname' => 'Moutou',
                'email' => 'jenna.moutou@lasentinelle.mu',
                'role' => UserRole::Salesperson,
            ],
            [
                'firstname' => 'Gino',
                'lastname' => 'Sophine',
                'email' => 'gino.sophie@lasentinelle.mu',
                'role' => UserRole::Salesperson,
            ],
            [
                'firstname' => 'Rachel',
                'lastname' => 'Dauhoo',
                'email' => 'rachel.dauhoo@lasentinelle.mu',
                'role' => UserRole::Salesperson,
            ],
            [
                'firstname' => 'Areff',
                'lastname' => 'Salauroo',
                'email' => 'areff.salauroo@lasentinelle.mu',
                'role' => UserRole::Management,
            ],
            [
                'firstname' => 'Enzo',
                'lastname' => 'Samuel',
                'email' => 'enzo.samuel@lasentinelle.mu',
                'role' => UserRole::Finance,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname'],
                    'password' => 'password',
                    'role' => $user['role'],
                ],
            );
        }
    }
}
