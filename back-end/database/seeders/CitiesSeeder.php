<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('cities')->insert([
            ["id"=>Str::uuid()->toString(), 
                'name'       => 'Abidjan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
