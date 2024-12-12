<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Create 10 suppliers with fake data
        for ($i = 0; $i < 10; $i++) {
            DB::table('suppliers')->insert([
                'name' => $faker->company,
                'code' => $faker->bothify('###-###'),
                'gst_no' => $faker->unique()->numerify('GST##########'),
                'cin_no' => $faker->unique()->bothify('L##########'),
                'pan_no' => $faker->unique()->bothify('?????#####'),
                'msme_no' => $faker->unique()->numerify('MSME##########'),
                'reg_address' => $faker->address,
                'work_address' => $faker->address,
                'area' => $faker->city,
                'tel_no' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                'owner_mobile' => $faker->numerify('##########'),
                'logo' => $faker->imageUrl(200, 200, 'business', true),
                'status' => $faker->boolean,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
