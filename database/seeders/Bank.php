<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Bank extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('banks')->insert([
            [
                'name' => 'HDFC Bank',
                'branch' => 'Main Branch',
                'ifsc_code' => 'IFSC1522236',
                'account_number' => '4777744569012',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
