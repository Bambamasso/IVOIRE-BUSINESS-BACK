<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Liste de toutes les permissions disponibles (pour l'écran Rôles & Permissions).
     */
    public function index()
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name', 'guard_name']);

        return response()->json([
            'status' => 'success',
            'data' => $permissions,
        ], 200);
    }
}
