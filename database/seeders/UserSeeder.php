<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Mohit Awasthi',
                'username' => 'mohitawasthi9695',
                'email' => 'mohitawasthi.intern@gmail.com',
                'phone' => '9234567890',
                'status' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('Password#123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'username' => 'Admin1234',
                'email' => 'admin@gmail.com',
                'phone' => '9876543210',
                'status' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('Password#123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vishals',
                'username' => 'Vishals123',
                'email' => 'super@gmail.com',
                'phone' => '9876543210',
                'status' => 1, // Inactive
                'email_verified_at' => now(),
                'password' => Hash::make('supervisor#123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Godown1',
                'username' => 'Godown1',
                'email' => 'godown1@gmail.com',
                'phone' => '9876543210',
                'status' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('godown1#123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Operator',
                'username' => 'Operator123',
                'email' => 'operator@gmail.com',
                'phone' => '9876543210',
                'status' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('operator#123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Godown2',
                'username' => 'Godown2',
                'email' => 'godown2@gmail.com',
                'phone' => '9876543210',
                'status' => 1,
                'email_verified_at' => now(),
                'password' => Hash::make('godown2#123'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
