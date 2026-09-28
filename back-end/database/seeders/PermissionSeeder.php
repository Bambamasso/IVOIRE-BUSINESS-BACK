<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Génère les permissions CRUD (+ actions spécifiques) d'un module.
     */
    private function makePerms(
        string $module,
        string $guard = 'api',
        array $extras = [],
        array $base = ['view', 'create', 'edit', 'delete'],
    ): array {
        $actions = array_merge($base, $extras);

        return array_map(
            fn ($action) => ['name' => "{$action}-{$module}", 'guard_name' => $guard],
            $actions,
        );
    }

    public function run(): void
    {
        $modules = [
            // Administration
            $this->makePerms('user'),
            $this->makePerms('role'),
            $this->makePerms('permission'),
            $this->makePerms('dashboard', base: ['view']),

            // Catalogue
            $this->makePerms('product'),
            $this->makePerms('attribute'),
            $this->makePerms('category'),

            // Commandes — pas de create/edit générique, seulement les transitions réelles
            $this->makePerms('order', extras: ['validate', 'cancel', 'deliver'], base: ['view']),

            // Demandes de service — idem, transitions du workflow réel
            $this->makePerms('service-request', extras: [
                'validate', 'start-processing', 'complete', 'reject',
            ], base: ['view', 'delete']),

            // Prestations & vitrine
            $this->makePerms('service'),
            $this->makePerms('project'),
            $this->makePerms('slide'),

            // Localisation
            $this->makePerms('city'),
            $this->makePerms('municipality'),
        ];

        $permissions = array_merge(...$modules);

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')
                ->where('name', $permission['name'])
                ->where('guard_name', $permission['guard_name'])
                ->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'id' => Str::uuid(),
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Les permissions viennent d'être insérées directement en base (hors Eloquent),
        // le cache interne de Spatie doit être vidé pour qu'elles soient reconnues aussitôt.
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
