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
        $orderType = StatusType::where('code', 'order')->first();
        $productType = StatusType::where('code', 'product')->first();
        $paymentType = StatusType::where('code', 'payment')->first();
        $serviceType = StatusType::where('code', 'service')->first();   

        $statuses = [
            // Statuts pour les Commandes
            ['status_type_id' => $orderType->id, 'name' => 'En attente', 'code' => 'pending'],
            ['status_type_id' => $orderType->id, 'name' => 'Validé(e)', 'code' => 'validated'],
            ['status_type_id' => $orderType->id, 'name' => 'Annulé(e)', 'code' => 'cancelled'],
            ['status_type_id' => $orderType->id, 'name' => 'Livrée', 'code' => 'delivered'],

            // Statuts pour les Produits
            ['status_type_id' => $productType->id, 'name' => 'Actif', 'code' => 'active'],
            ['status_type_id' => $productType->id, 'name' => 'En rupture', 'code' => 'out-of-stock'],
            ['status_type_id' => $productType->id, 'name' => 'Archivé', 'code' => 'archived'],
            ['status_type_id' => $productType->id, 'name' => 'Annulé(e)', 'code' => 'canceled'],

            // Statuts pour les Paiements
            ['status_type_id' => $paymentType->id, 'name' => 'Impayé', 'code' => 'unpaid'],
            ['status_type_id' => $paymentType->id, 'name' => 'Payé', 'code' => 'paid'],
            

            // Statuts pour les Services
            ['status_type_id' => $serviceType->id, 'name' => 'En attente', 'code' => 'pending'],
            ['status_type_id' => $serviceType->id, 'name' => 'En cours', 'code' => 'in-progress'],
            ['status_type_id' => $serviceType->id, 'name' => 'Terminé', 'code' => 'completed'],
            ['status_type_id' => $serviceType->id, 'name' => 'Annulé(e)', 'code' => 'cancelled'],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(['code' => $status['code']], $status);
        }
    }
}
