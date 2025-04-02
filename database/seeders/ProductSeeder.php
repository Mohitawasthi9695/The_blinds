<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $fakeRecords = [];

        for ($i = 0; $i < 10000; $i++) {
            $fakeRecords[] = [
                'product_category_id' => $faker->numberBetween(1, 5),
                'name' => $faker->word,
                'date' => $faker->date(),
                'shadeNo' => $faker->randomNumber(5),
                'purchase_shade_no' => $faker->randomNumber(5),
                'status' => $faker->randomElement([1, 1]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in chunks to avoid memory overflow
        foreach (array_chunk($fakeRecords, 1000) as $chunk) {
            DB::table('products')->insert($chunk);
        }
    }
}
