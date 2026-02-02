<?php

namespace Database\Seeders;

use App\Models\StatusType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $types = [
            ['name' => 'Commande', 'slug' => 'order'],
            ['name' => 'Paiement', 'slug' => 'payment'],
            ['name' => 'Produit', 'slug' => 'product'],
            
        ];

        foreach ($types as $type) {
            StatusType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
    
}
