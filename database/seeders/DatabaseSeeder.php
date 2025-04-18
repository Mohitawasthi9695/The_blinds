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
            ProductCategory::class,
            // PeopleSeeder::class,
            // StockInvoice::class,  
            // ProductSeeder::class, 
        ]);
    }
}
