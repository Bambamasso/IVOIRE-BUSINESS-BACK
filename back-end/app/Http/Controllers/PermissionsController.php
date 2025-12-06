<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    //
   public function assignPermissions(PermissionRequest $request, $roleId)
{
    $input = $request->validated();
    
    $role = Role::findOrFail($roleId);

    $role->syncPermissions($input['permissions']);

    return response()->json([
        "status" => "success",
        'message' => 'Permissions assigned successfully.',
        'role' => $role,
        'permissions' => $role->permissions,
    ]);
}

    /**
     * Ajouter une seule permission à un rôle
     */
    public function addPermission(Request $request, $roleId)
    {
        $request->validate([
            'permission' => 'required|string',
        ]);

        $role = Role::findOrFail($roleId);
        $role->givePermissionTo($request->permission);

        return response()->json([
            'message' => 'Permission added.',
        ]);
    }

    /**
     * Enlever une permission
     */
    public function removePermission(Request $request, $roleId)
    {
        $request->validate([
            'permission' => 'required|string',
        ]);

        $role = Role::findOrFail($roleId);
        $role->revokePermissionTo($request->permission);

        return response()->json([
            'message' => 'Permission removed.',
        ]);
    }

    /**
     * Lister les permissions d’un rôle
     */
    public function listPermissions($roleId)
    {
        $role = Role::findOrFail($roleId);

        return response()->json([
            'role' => $role->name,
            'permissions' => $role->permissions,
        ]);
    }
}
