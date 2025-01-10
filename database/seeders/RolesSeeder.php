<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role_superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $role_admin = Role::firstOrCreate(['name' => 'admin']);
        $role_supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $role_sub_supervisor = Role::firstOrCreate(['name' => 'sub_supervisor']);
        $role_operator = Role::firstOrCreate(['name' => 'operator']);
    }
}
