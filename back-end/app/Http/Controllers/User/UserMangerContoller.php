<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserMangerContoller extends Controller
{
    //
    public function getUser()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => [
                'users' => $users->load('roles'),
            ],
        ], 200);
    }

    public function updateRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($userId);
        $user->syncRoles([$request->role]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rôle mis à jour avec succès',
            'data' => [
                'user' => $user->load('roles'),
            ],
        ], 200);
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'l\'utilisateur supprimé  avec succès',
        ], 200);
    }
}
