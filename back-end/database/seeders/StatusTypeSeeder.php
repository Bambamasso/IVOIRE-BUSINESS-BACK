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
        $types = [
            ['name' => 'Commande', 'code' => 'order'],
            ['name' => 'Produit', 'code' => 'product'],
            ['name' => 'Paiement', 'code' => 'payment'],
            ['name' => 'Service', 'code' => 'service'],
            ['name' => 'Statut documents', 'code' => 'doc_status']
        ];

        foreach ($types as $type) {
            StatusType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
