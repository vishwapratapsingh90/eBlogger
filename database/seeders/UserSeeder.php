<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = [
            [
                'name' => 'Motu',
                'email' => 'motu@yopmail.com',
                'password'=> 'Password@12345',
            ],
            [
                'name'=> 'Patlu',
                'email' => 'patlu@yopmail.com',
                'password'=> 'Password@12345',
            ]
        ];

        foreach ($users as $user) {
            $user['password'] = bcrypt($user['password']);

            if (User::where('email', $user['email'])->exists()) {
                continue;
            }

            try {
                User::factory()->create([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Ignore duplicate or constraint errors and continue seeding.
                continue;
            }
        }
    }
}
