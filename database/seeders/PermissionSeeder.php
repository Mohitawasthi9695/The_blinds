<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'stockInvoice',
            'stockInvoice.create',
            'stockInvoice.view',
            'stockInvoice.edit',
            'stockInvoice.delete',
            'stockIn.create',
            'stockIn.view',
            'stockIn.edit',
            'stockIn.delete',
            'stockOut.create',
            'stockOut.view',
            'stockOut.edit',
            'stockOut.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        $role_admin = Role::firstOrCreate(['name' => 'admin']);
        $role_superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $role_admin->syncPermissions(Permission::all());
        $role_superadmin->syncPermissions(Permission::all());

        $superadminUsers = User::role('superadmin')->get();
        foreach ($superadminUsers as $user2) {
            $user2->syncPermissions(Permission::all());
        }
    }
}
