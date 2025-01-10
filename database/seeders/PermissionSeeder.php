<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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

        $superadminUsers = User::role('superadmin')->get();
        if (!empty($superadminUsers)) {
            foreach ($superadminUsers as $user) {
                $user->syncPermissions(Permission::all());
            }
        }
        $supervisor = User::role('supervisor')->get();
        if (!empty($supervisor)) {
            foreach ($supervisor as $user) {
                $user->syncPermissions(Permission::all());
            }
        }
    }
}
