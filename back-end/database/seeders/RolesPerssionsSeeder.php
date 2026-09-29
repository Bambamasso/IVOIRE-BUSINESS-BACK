<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
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
        // Réinitialiser le cache des permissions au début
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'api';

        $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();
        $managerRole = Role::where('name', 'manager')->where('guard_name', $guard)->first();
        $superAdminRole = Role::where('name', 'super-admin')->where('guard_name', $guard)->first();

        if (!$adminRole || !$managerRole || !$superAdminRole) {
            $this->command->error("Les rôles 'admin', 'manager' et/ou 'super-admin' sont introuvables. Exécutez d'abord RoleSeeder.");
            return;
        }

        // Récupérer directement la collection de modèles Permission
        $allPermissions = Permission::where('guard_name', $guard)->get();

        if ($allPermissions->isEmpty()) {
            $this->command->error("Aucune permission trouvée. Exécutez d'abord PermissionSeeder.");
            return;
        }

        // Administrateur : accès total.
        $adminRole->syncPermissions($allPermissions);
        $superAdminRole->syncPermissions($allPermissions);

        // Superviseur (gestionnaire) : tout, sauf créer/modifier/supprimer des utilisateurs.
        $userManagementOnly = ['create-user', 'edit-user', 'delete-user'];
        $managerPermissions = $allPermissions->reject(
            fn(Permission $permission) => in_array($permission->name, $userManagementOnly, true)
        );

        $managerRole->syncPermissions($managerPermissions);

        // Ré-attribue le rôle admin au compte de développement s'il existe déjà
        // User::where('email', 'admin@gmail.com')->first()?->assignRole($adminRole);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
