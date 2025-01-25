<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('products')->insert([
            [
                'product_category_id' => 1,
                'name' => 'New Zebra',
                'shadeNo' => 'Shade001',
                'purchase_shade_no' => 'PSN001',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category_id' => 1,
                'name' => 'New Zebra',
                'shadeNo' => 'Shade002',
                'purchase_shade_no' => 'PSN002',
                'status' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category_id' => 2,
                'name' => 'Vento',
                'shadeNo' => 'Shade003',
                'purchase_shade_no' => 'PSN003',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
