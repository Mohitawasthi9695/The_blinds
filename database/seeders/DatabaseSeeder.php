<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RolesSeeder::class,
            PermissionSeeder::class,
            SupplierSeeder::class,
            ReceiverSeeder::class,
            ProductCategory::class,
            ProductSeeder::class,
            StockInvoice::class,   
        ]);
    }
}
