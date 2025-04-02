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
            'change-password',
            'peoples.create',
            'peoples.view',
            'peoples.edit',
            'peoples.delete',
            'sub_supervisor.view',
            'data.view',
            'recent-suppliers.view',
            'products.category.view',
            'products.category.create',
            'products.category.edit',
            'products.category.delete',
            'products.create',
            'products.view',
            'products.edit',
            'products.delete',
            'product.import-csv',
            'productshadeno.view',
            'stockin.invoice.create',
            'stockin.invoice.view',
            'stockin.invoice.edit',
            'stockin.invoice.delete',
            'stocks.create',
            'stocks.view',
            'stocks.edit',
            'stocks.delete',
            'stockout.invoiceno.view',
            'stocks.import-csv',
            'category.getstock.view',
            'gatepassno.view',
            'getaccessorycode.view',
            'godownaccessoryout.create',
            'getstocks.view',
            'godowns.gatepass.create',
            'godowns.getStockgatepass.view',
            'godowns.getStockgatepass.id.view',
            'godowns.gatepass.delete',
            'godownstock.view',
            'godownstock.create',
            'godownstock.edit',
            'godownstock.delete',
            'godownverticalstock.view',
            'godownverticalstock.create',
            'godownverticalstock.stock.view',
            'accessory.getStockgatepass.view',
            'accessory.getStockgatepass.id.view',
            'godowns.gatepass.edit',
            'godowns.gatepass.approve',
            'godowns.gatepass.reject',
            'accessory.create',
            'accessory.view',
            'accessory.edit',
            'accessory.delete',
            'accessory.import-excel',
            'accessory.category.view',
            'warehouseAccessory.create',
            'warehouseAccessory.view',
            'warehouseAccessory.edit',
            'warehouseAccessory.delete',
            'warehouseAccessory.import-file',
            'warehouse.accessory.category.view',
            'godowns.accessory.gatepass.create',
            'godowns.accessory.gatepass.approve',
            'godownAccessory.create',
            'godownAccessory.view',
            'godownAccessory.edit',
            'godownAccessory.delete',
            'getgodownstocks.view',
            'godownstockout.create',
            'godownstockout.approve',
            'godownstockout.view',
            'godownstockout.delete',
            'stockout.view',
            'godownout.view',
            'godownout.edit',
            'accessoryout.view',
            'accessoryout.id.view',
            'gettranferstocks.view',
            'godowns.transfergatepass.create',
            'godowns.transferstocks.view',
            'gettranferaccessory.view',
            'godowns.transfer.accessorygatepass.create',
            'sales.view',
            'stockin.view',
            'stockOuttoday.view',
            'barData.view'
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
