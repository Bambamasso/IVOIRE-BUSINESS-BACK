<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $attributesData = [
            'Couleur' => [
                'Rouge', 'Bleu', 'Vert', 'Noir', 'Blanc', 'Jaune', 'Gris', 'Rose'
            ],
            'Taille' => [
                'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'
            ],
            'Pointure' => [ // Pour les chaussures
                '36', '37', '38', '39', '40', '41', '42', '43', '44', '45'
            ],
            'Matière' => [
                'Coton', 'Polyester', 'Soie', 'Cuir', 'Jean'
            ],
            'Stockage' => [ // Exemple pour l'électronique si tu en fais
                '64 Go', '128 Go', '256 Go', '512 Go', '1 To'
            ]
        ];

        // On boucle pour insérer les données
        foreach ($attributesData as $attributeName => $values) {
            
            // 1. Créer l'Attribut (Le parent)
            // firstOrCreate évite les doublons si tu lances le seeder 2 fois
            $attribute = Attribute::firstOrCreate(['name' => $attributeName
            ],['slug' => Str::slug($attributeName)]);

            // 2. Créer les Valeurs liées (Les enfants)
            foreach ($values as $value) {
                AttributeValue::firstOrCreate([
                    'attribute_id' => $attribute->id,
                    'value'        => $value,
                    
                ]);
            }
        }
    }
    
}
