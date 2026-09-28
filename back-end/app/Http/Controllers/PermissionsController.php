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
    //ajouter plusieurs permission à un rôles
   public function assignPermissions(PermissionRequest $request, $roleId)
{
    $input = $request->validated();

    $role = Role::findOrFail($roleId);

    $role->syncPermissions($input['permissions'] ?? []);

    return response()->json([
        "status" => "success",
        'message' => 'Permissions mises à jour avec succès.',
        'role' => $role,
        'permissions' => $role->permissions,
    ]);
}

    /**
     * Ajouter une seule permission à un rôle
     */
    public function addPermission(Request $request, $roleId)
    {
        $validator = validator($request->all(), [
            'permission' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::findOrFail($roleId);
        $role->givePermissionTo($validator->validated()['permission']);

        return response()->json([
            "status"=>"success",
            'message' => 'Permission ajoutée.',
        ],201);
    }

    /**
     * Enlever une permission
     */
    public function removePermission(Request $request, $roleId)
    {
        $validator = validator($request->all(), [
            'permission' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::findOrFail($roleId);
        $role->revokePermissionTo($validator->validated()['permission']);

        return response()->json([
            'status'=>'success',
            'message' => 'Permission retirée.',
            'data'=>$role,
        ],200);
    }

    /**
     * Lister les permissions d’un rôle
     */
    public function listPermissions($roleId)
    {
        $role = Role::findOrFail($roleId);

        return response()->json([
            'status'=>'success',
            'role' => $role->name,
            'permissions' => $role->permissions,
        ],200);
    }
}
