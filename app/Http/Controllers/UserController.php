<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $users = User::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Utilisateurs récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("UserController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des utilisateurs'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'phone' => 'nullable|string|max:20',
                'is_2fa_enable' => 'nullable|boolean',
                'provider' => 'nullable|string|max:255',
                'provider_id' => 'nullable|string|max:255',
                'status' => 'nullable|in:active,inactive,banned',
                'image' => 'nullable|string',
                'role' => 'nullable|in:user,admin,superadmin',
            ]);

            // Hash du mot de passe
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Utilisateur créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("UserController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création de l\'utilisateur'
            ], 500);
        }
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Utilisateur récupéré avec succès'
        ]);
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id)
                ],
                'password' => 'nullable|string|min:8',
                'phone' => 'nullable|string|max:20',
                'is_2fa_enable' => 'nullable|boolean',
                'provider' => 'nullable|string|max:255',
                'provider_id' => 'nullable|string|max:255',
                'status' => 'nullable|in:active,inactive,banned',
                'image' => 'nullable|string',
                'role' => 'nullable|in:user,admin,superadmin',
            ]);

            // Hash du mot de passe si fourni
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Utilisateur mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("UserController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur'
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("UserController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de l\'utilisateur'
            ], 500);
        }
    }
}