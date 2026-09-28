<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\NewUserCredentialsMail;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserMangerContoller extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);

        $query = User::with('roles')->orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $role = $request->query('role');
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ], 200);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $role = $data['role'];
        unset($data['role']);

        $data['username'] = $data['username'] ?? $this->uniqueUsername($data['first_name']);
        $plainPassword = $data['password'] ?? $this->generatePassword();
        $data['password'] = $plainPassword;

        $user = User::create($data);
        $user->assignRole($role);

        $this->safeMail(fn () => Mail::to($user->email)->send(
            new NewUserCredentialsMail($user->username, $plainPassword)
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur créé avec succès.',
            'data' => $user->load('roles'),
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->load('roles'),
        ], 200);
    }

    
    public function profile()
    {
        $user = auth()->user()->load('roles');

        return response()->json([
            'status' => 'success',
            'data' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 200);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $role = $data['role'] ?? null;
        unset($data['role']);

       
        $firstNameChanged = isset($data['first_name']) && $data['first_name'] !== $user->first_name;
        $plainPassword = null;

        if ($firstNameChanged && !isset($data['username'])) {
            $data['username'] = $this->uniqueUsername($data['first_name']);
            $plainPassword = $this->generatePassword();
            $data['password'] = $plainPassword;
        }

        $user->update($data);

        if ($role) {
            $user->syncRoles([$role]);
        }

        if ($plainPassword) {
            $this->safeMail(fn () => Mail::to($user->email)->send(
                new NewUserCredentialsMail($user->username, $plainPassword)
            ));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => $user->fresh()->load('roles'),
        ], 200);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => "L'utilisateur a été supprimé avec succès.",
        ], 200);
    }

    public function resetPassword(User $user)
    {
        $password = $this->generatePassword();
        $user->update(['password' => $password]);

        $this->safeMail(fn () => Mail::to($user->email)->send(
            new ResetPasswordMail($user->username, $password)
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe réinitialisé avec succès.',
        ], 200);
    }

    
    private function safeMail(callable $send): void
    {
        try {
            $send();
        } catch (\Throwable $e) {
            Log::error("Échec d'envoi d'e-mail utilisateur : " . $e->getMessage());
        }
    }

    private function uniqueUsername(string $firstName): string
    {
        $base = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $firstName)) ?: 'user';

        do {
            $username = $base . rand(1000, 9999);
        } while (User::where('username', $username)->exists());

        return $username;
    }

    private function generatePassword(): string
    {
        return Str::random(12);
    }
}
