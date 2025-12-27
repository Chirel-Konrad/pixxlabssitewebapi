<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponse;
    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     tags={"Users"},
     *     summary="Liste des utilisateurs",
     *     description="Récupère la liste paginée des utilisateurs",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="message", type="string", example="Utilisateurs récupérés avec succès")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $users = User::latest()->paginate($perPage);

            return $this->paginatedResponse($users, UserResource::class, 'Utilisateurs récupérés avec succès');
        } catch (\Exception $e) {
            Log::error("UserController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des utilisateurs', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users",
     *     tags={"Users"},
     *     summary="Créer un utilisateur",
     *     description="Crée un nouvel utilisateur (Admin seulement)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!"),
     *             @OA\Property(property="phone", type="string", example="+33612345678"),
     *             @OA\Property(property="role", type="string", enum={"user", "admin"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Utilisateur créé avec succès")
     *         )
     *     )
     * )
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();

            // Hash du mot de passe
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return $this->successResponse(new UserResource($user), 'Utilisateur créé avec succès', 201);
        } catch (\Exception $e) {
            Log::error("UserController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création de l\'utilisateur', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/{user}",
     *     tags={"Users"},
     *     summary="Détails d'un utilisateur",
     *     description="Récupère les détails d'un utilisateur spécifique",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Utilisateur récupéré avec succès")
     *         )
     *     )
     * )
     */
    public function show(User $user)
    {
        return $this->successResponse(new UserResource($user), 'Utilisateur récupéré avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/users/{user}",
     *     tags={"Users"},
     *     summary="Mettre à jour un utilisateur",
     *     description="Met à jour les informations d'un utilisateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *             @OA\Property(property="phone", type="string", example="+33698765432"),
     *             @OA\Property(property="role", type="string", enum={"user", "admin"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Utilisateur mis à jour avec succès")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $validated = $request->validated();

            // Hash du mot de passe si fourni
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return $this->successResponse(new UserResource($user), 'Utilisateur mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error("UserController@update: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour de l\'utilisateur', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/users/{user}",
     *     tags={"Users"},
     *     summary="Supprimer un utilisateur",
     *     description="Supprime un utilisateur définitivement",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Utilisateur supprimé avec succès")
     *         )
     *     )
     * )
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return $this->successResponse(null, 'Utilisateur supprimé avec succès');
        } catch (\Exception $e) {
            Log::error("UserController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression de l\'utilisateur', 500, $e->getMessage());
        }
    }
    /**
     * @OA\Patch(
     *     path="/api/v1/admin/users/{user}/ban",
     *     tags={"Users"},
     *     summary="Bannir un utilisateur",
     *     description="Change le statut de l'utilisateur à 'banned'. L'utilisateur ne pourra plus se connecter.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur banni avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Utilisateur banni avec succès")
     *         )
     *     )
     * )
     */
    public function ban(User $user)
    {
        try {
            $user->update(['status' => 'banned']);
            return $this->successResponse(new UserResource($user), 'Utilisateur banni avec succès');
        } catch (\Exception $e) {
            Log::error("UserController@ban: " . $e->getMessage());
            return $this->errorResponse('Erreur lors du bannissement de l\'utilisateur', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/admin/users/{user}/unban",
     *     tags={"Users"},
     *     summary="Débannir un utilisateur",
     *     description="Change le statut de l'utilisateur à 'active'. L'utilisateur pourra de nouveau se connecter.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur débanni avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="Utilisateur débanni avec succès")
     *         )
     *     )
     * )
     */
    public function unban(User $user)
    {
        try {
            $user->update(['status' => 'active']);
            return $this->successResponse(new UserResource($user), 'Utilisateur débanni avec succès');
        } catch (\Exception $e) {
            Log::error("UserController@unban: " . $e->getMessage());
            return $this->errorResponse('Erreur lors du débannissement de l\'utilisateur', 500, $e->getMessage());
        }
    }
}