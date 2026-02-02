<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\StatusType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        // On récupère les types pour avoir leurs IDs
        $orderType = StatusType::where('slug', 'order')->first();
        $productType = StatusType::where('slug', 'product')->first();

        $statuses = [
            // Statuts pour les Commandes
            ['status_type_id' => $orderType->id, 'name' => 'En attente', 'slug' => 'pending'],
            ['status_type_id' => $orderType->id, 'name' => 'Payée', 'slug' => 'paid'],
            ['status_type_id' => $orderType->id, 'name' => 'Expédiée', 'slug' => 'shipped'],
            ['status_type_id' => $orderType->id, 'name' => 'Expédiée', 'slug' => 'shipped'],
            
            // Statuts pour les Produits
            ['status_type_id' => $productType->id, 'name' => 'Actif', 'slug' => 'active'],
            ['status_type_id' => $productType->id, 'name' => 'En rupture', 'slug' => 'out-of-stock'],
            ['status_type_id' => $productType->id, 'name' => 'Archivé', 'slug' => 'archived'],
            ['status_type_id' => $productType->id, 'name' => 'annulé(e)', 'slug' => 'canceled'],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
