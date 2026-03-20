<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Albin Andersson', 'email' => 'albin@homeplanner.app'],
            ['name' => 'Magnus', 'email' => 'magnus@homeplanner.app'],
            ['name' => 'Sofia', 'email' => 'sofia@homeplanner.app'],
            ['name' => 'Magnus Schöllin', 'email' => 'magnus.s@homeplanner.app'],
            ['name' => 'Luna', 'email' => 'luna@homeplanner.app'],
            ['name' => 'Theo', 'email' => 'theo@homeplanner.app'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
