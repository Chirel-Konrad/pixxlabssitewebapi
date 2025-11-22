<?php

namespace App\Http\Controllers;

use App\Models\Privilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrivilegeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/privileges",
     *     tags={"Privileges"},
     *     summary="Liste des privilèges",
     *     description="Récupère la liste paginée des privilèges",
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
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Privilege")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $privileges = Privilege::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $privileges,
                'message' => 'Privilèges récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des privilèges'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/privileges",
     *     tags={"Privileges"},
     *     summary="Créer un privilège",
     *     description="Crée un nouveau privilège",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title"},
     *                 @OA\Property(property="title", type="string", example="Privilège VIP"),
     *                 @OA\Property(property="description", type="string", example="Accès exclusif..."),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Privilège créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Privilege"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
            ]);

            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
            if ($request->hasFile("image")) {
                $validated['image'] = $request->file("image")->store("privileges", "public");
            }

            $privilege = Privilege::create($validated);

            return response()->json([
                'success' => true,
                'data' => $privilege,
                'message' => 'Privilège créé avec succès'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du privilège'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/privileges/{privilege}",
     *     tags={"Privileges"},
     *     summary="Détails d'un privilège par ID",
     *     description="Récupère les détails d'un privilège via son ID.",
     *     @OA\Parameter(
     *         name="privilege",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privilège trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Privilege"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/privileges/slug/{slug}",
     *     tags={"Privileges"},
     *     summary="Détails d'un privilège par Slug",
     *     description="Récupère les détails d'un privilège via son slug.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privilège trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Privilege"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Privilege $privilege)
    {
        return response()->json([
            'success' => true,
            'data' => $privilege,
            'message' => 'Privilège récupéré avec succès'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/privileges/{privilege}",
     *     tags={"Privileges"},
     *     summary="Mettre à jour un privilège par ID",
     *     description="Met à jour un privilège existant via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="privilege",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privilège mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Privilege"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/privileges/slug/{slug}",
     *     tags={"Privileges"},
     *     summary="Mettre à jour un privilège par Slug",
     *     description="Met à jour un privilège existant via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privilège mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Privilege"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Privilege $privilege)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
            ]);

            // Ne plus modifier le slug lors de la mise à jour

            if ($request->hasFile("image")) {
                if ($privilege->image) {
                    Storage::disk("public")->delete($privilege->image);
                }
                $validated['image'] = $request->file("image")->store("privileges", "public");
            }

            $privilege->update($validated);

            return response()->json([
                'success' => true,
                'data' => $privilege,
                'message' => 'Privilège mis à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du privilège'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/privileges/{privilege}",
     *     tags={"Privileges"},
     *     summary="Supprimer un privilège par ID",
     *     description="Supprime un privilège via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="privilege",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privilège supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/privileges/slug/{slug}",
     *     tags={"Privileges"},
     *     summary="Supprimer un privilège par Slug",
     *     description="Supprime un privilège via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privilège supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Privilege $privilege)
    {
        try {
            if ($privilege->image) {
                Storage::disk("public")->delete($privilege->image);
            }
            $privilege->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Privilège supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du privilège'
            ], 500);
        }
    }
}
