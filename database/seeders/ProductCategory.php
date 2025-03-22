<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategory extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_categories')->insert([
            [
                'product_category'=>'Roller',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category'=>'Wooden',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category'=>'Vertical',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category'=>'Honey Comb',
                'created_at' => now(),
                'updated_at' => now(),
            ], [
                'product_category'=>'Zebra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_category'=>'chicks',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
