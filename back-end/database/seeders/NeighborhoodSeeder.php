<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NeighborhoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         $abidjanId = DB::table('cities')->where('name', 'Abidjan')->value('id');

        $communes = [
            // Communes du Plateau / Centre
            ['name' => 'Le Plateau',         'shipping_fee' => 1500],
            ['name' => 'Adjamé',             'shipping_fee' => 1500],
            ['name' => 'Attécoubé',          'shipping_fee' => 1500],

            // Communes Nord
            ['name' => 'Abobo',              'shipping_fee' => 2000],
            ['name' => 'Anyama',             'shipping_fee' => 2500],

            // Communes Sud / Lagune
            ['name' => 'Treichville',        'shipping_fee' => 1500],
            ['name' => 'Marcory',            'shipping_fee' => 1500],
            ['name' => 'Koumassi',           'shipping_fee' => 2000],
            ['name' => 'Port-Bouët',         'shipping_fee' => 2000],

            // Communes Ouest
            ['name' => 'Yopougon',           'shipping_fee' => 2000],

            // Communes Est / Zone 4
            ['name' => 'Cocody',             'shipping_fee' => 1500],
            ['name' => 'Bingerville',        'shipping_fee' => 2500],

            // Communes Sud-Est
            ['name' => 'Williamsville',      'shipping_fee' => 1500],
        ];

        $data = array_map(function ($commune) use ($abidjanId) {
            return [
                "id"=>Str::uuid()->toString(), 
                'city_id'      => $abidjanId,
                'name'         => $commune['name'],
                'shipping_fee' => $commune['shipping_fee'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }, $communes);

        DB::table('municipalities')->insert($data);
    }
}
