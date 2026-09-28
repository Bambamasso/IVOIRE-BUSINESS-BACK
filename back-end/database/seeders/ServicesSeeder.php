<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
      $services = [

           [
            "id" => Str::uuid()->toString(),
            "name" => "Service de nettoyage",
            "description" => "Un service de nettoyage professionnel pour votre maison ou votre bureau.",
            "price" => 10000.00,
            ],
        

        [
            "id" => Str::uuid()->toString(),
            "name" => "Service de jardinage",
            "description" => "Un service de jardinage pour entretenir votre jardin et vos espaces verts.",
            "price" => 15000.00,
        ],
        [
            "id" => Str::uuid()->toString(),
            "name" => "Service de réparation informatique",
            "description" => "Un service de réparation informatique pour résoudre les problèmes de votre ordinateur ou de vos appareils électroniques.",
            "price" => 20000.00,
        ]
        ];
      
        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['name' => $service['name']],
                $service
            );
        }
        
    }
}
