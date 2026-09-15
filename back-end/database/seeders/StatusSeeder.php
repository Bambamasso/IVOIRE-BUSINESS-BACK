<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\StatusType;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Récupération des types
        $orderType     = StatusType::where('code', 'order')->first();
        $productType   = StatusType::where('code', 'product')->first();
        $paymentType   = StatusType::where('code', 'payment')->first();
        $serviceType   = StatusType::where('code', 'service')->first(); 
        $docStatusType = StatusType::where('code', 'doc_status')->first(); 

        // Sécurité : vérifier que tous les StatusTypes existent
        if (!$orderType || !$productType || !$paymentType || !$serviceType || !$docStatusType) {
            $this->command->error("Certains StatusType sont introuvables. Exécutez d'abord le StatusTypeSeeder !");
            return;
        }

        $statuses = [
            // Statuts pour les Commandes
            ['status_type_id' => $orderType->id, 'name' => 'En attente', 'code' => 'pending'],
            ['status_type_id' => $orderType->id, 'name' => 'Validé(e)', 'code' => 'validated'],
            ['status_type_id' => $orderType->id, 'name' => 'Annulé(e)', 'code' => 'cancelled'],
            ['status_type_id' => $orderType->id, 'name' => 'Livrée', 'code' => 'delivered'],

            // Statuts pour les Produits
            ['status_type_id' => $productType->id, 'name' => 'Actif', 'code' => 'available'],
            ['status_type_id' => $productType->id, 'name' => 'En rupture', 'code' => 'out-of-stock'],
            ['status_type_id' => $productType->id, 'name' => 'Archivé', 'code' => 'archived'],
            ['status_type_id' => $productType->id, 'name' => 'Annulé(e)', 'code' => 'canceled'],

            // Statuts pour les Paiements
            ['status_type_id' => $paymentType->id, 'name' => 'Impayé', 'code' => 'unpaid'],
            ['status_type_id' => $paymentType->id, 'name' => 'Payé', 'code' => 'paid'],
            ['status_type_id' => $paymentType->id, 'name' => 'Remboursé', 'code' => 'refunded'],

            // Statuts pour les Services
            ['status_type_id' => $serviceType->id, 'name' => 'En attente', 'code' => 'pending'],
            ['status_type_id' => $serviceType->id, 'name' => 'Validée', 'code' => 'validated'],
            ['status_type_id' => $serviceType->id, 'name' => 'En cours', 'code' => 'in-progress'],
            ['status_type_id' => $serviceType->id, 'name' => 'Terminé', 'code' => 'completed'],
            ['status_type_id' => $serviceType->id, 'name' => 'Annulé(e)', 'code' => 'cancelled'],

            // Statuts pour les Documents (doc_status)
            ['status_type_id' => $docStatusType->id, 'name' => 'Devis envoyé', 'code' => 'quote_sent'],
            ['status_type_id' => $docStatusType->id, 'name' => 'Devis accepté', 'code' => 'quote_accepted'],
            ['status_type_id' => $docStatusType->id, 'name' => 'Devis refusé', 'code' => 'quote_rejected'],
            ['status_type_id' => $docStatusType->id, 'name' => 'Proforma envoyée', 'code' => 'proforma_sent'],
            ['status_type_id' => $docStatusType->id, 'name' => 'Commande confirmée', 'code' => 'order_confirmed'],
        ];

        foreach ($statuses as $status) {
            // Un statut est unique par la combinaison de son CODE et de son TYPE
            Status::updateOrCreate(
                [
                    'code' => $status['code'],
                    'status_type_id' => $status['status_type_id'],
                ],
                $status
            );
        }
    }
}