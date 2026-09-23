<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPerssionsSeeder extends Seeder
{
    /**
     * Attribue les permissions aux rôles :
     * - admin (Administrateur) : toutes les permissions.
     * - manager (Superviseur) : toutes les permissions, sauf la gestion des comptes utilisateurs.
     */
    public function run(): void
    {
        $guard = 'api';

        $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();
        $managerRole = Role::where('name', 'manager')->where('guard_name', $guard)->first();

        if (!$adminRole || !$managerRole) {
            $this->command->error("Les rôles 'admin' et/ou 'manager' sont introuvables. Exécutez d'abord RoleSeeder.");
            return;
        }

        $allPermissions = Permission::where('guard_name', $guard)->pluck('name');

        if ($allPermissions->isEmpty()) {
            $this->command->error("Aucune permission trouvée. Exécutez d'abord PermissionSeeder.");
            return;
        }

        // Administrateur : accès total.
        $adminRole->syncPermissions($allPermissions);

        // Superviseur (gestionnaire) : tout, sauf créer/modifier/supprimer des utilisateurs.
        $userManagementOnly = ['create-user', 'edit-user', 'delete-user'];
        $managerPermissions = $allPermissions->reject(
            fn (string $name) => in_array($name, $userManagementOnly, true)
        );
        $managerRole->syncPermissions($managerPermissions);

        // Ré-attribue le rôle admin au compte de développement s'il existe déjà,
        // pour ne pas perdre l'accès admin à chaque réinitialisation de la base.
        User::where('email', 'admin@gmail.com')->first()?->assignRole($adminRole);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
