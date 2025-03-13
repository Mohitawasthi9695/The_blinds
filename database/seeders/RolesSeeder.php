<?php

namespace Database\Seeders;

use App\Models\User;
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
        $superadmin = User::find(1);
        $superadmin->assignRole('superadmin');
        $admin = User::find(2);
        $admin->assignRole('admin');
        $supervisor = User::find(3);
        $supervisor->assignRole('supervisor');
        $sub_supervisor = User::find(4);
        $sub_supervisor->assignRole('sub_supervisor');
        $sub_supervisor = User::find(6);
        $sub_supervisor->assignRole('sub_supervisor');
        $operator = User::find(5);
        $operator->assignRole('operator');
        
    }
}
