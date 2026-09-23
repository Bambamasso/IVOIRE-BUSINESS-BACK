<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Liste des rôles avec leurs permissions (pour l'écran Rôles & Permissions).
     */
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ], 200);
    }

    public function show(Role $role)
    {
        return response()->json([
            'status' => 'success',
            'data' => $role->load('permissions'),
        ], 200);
    }
}
