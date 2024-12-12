<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Mohit Awasthi',
            'username' => 'mohitawasthi9695',
            'email' => 'mohitawasthi.intern@gmail.com',
            'phone' => '9234567890',
            'status' => 0, // Active
            'role' => 1, // CMP
            'email_verified_at' => now(),
            'password' => Hash::make('Password#123'),
            'remember_token' => str::random(10),
        ]);

        User::create([
            'name' => 'Admin',
            'username' => 'Admin1234',
            'email' => 'admin@gmail.com',
            'phone' => '9876543210',
            'status' => 0, // Inactive
            'role' => 2, // Admin
            'email_verified_at' => now(),
            'password' => Hash::make('Password#456'),
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'name' => 'Supervisor',
            'username' => 'Super123',
            'email' => 'super@gmail.com',
            'phone' => '9876543210',
            'status' => 0, // Inactive
            'role' => 3, // Supervisor
            'email_verified_at' => now(),
            'password' => Hash::make('Password#678'),
            'remember_token' => Str::random(10),
        ]);
        User::create([
            'name' => 'Operator',
            'username' => 'Operator123',
            'email' => 'operator@gmail.com',
            'phone' => '9876543210',
            'status' => 0, // Inactive
            'role' => 4, // Operator
            'email_verified_at' => now(),
            'password' => Hash::make('Password#910'),
            'remember_token' => Str::random(10),
        ]);


    }
}
