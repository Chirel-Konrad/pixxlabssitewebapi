<?php

namespace App\Http\Controllers;

use App\Models\Pilier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PilierController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/piliers",
     *     tags={"Piliers"},
     *     summary="Liste des piliers",
     *     description="Récupère la liste paginée des piliers",
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
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Pilier")),
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
            $piliers = Pilier::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $piliers,
                'message' => 'Piliers récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PilierController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des piliers'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/piliers",
     *     tags={"Piliers"},
     *     summary="Créer un pilier",
     *     description="Crée un nouveau pilier",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description"},
     *                 @OA\Property(property="title", type="string", example="Pilier 1"),
     *                 @OA\Property(property="description", type="string", example="Description du pilier"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pilier créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Pilier"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('piliers', 'public');
            }

            $pilier = Pilier::create($validated);

            return response()->json([
                'success' => true,
                'data' => $pilier,
                'message' => 'Pilier créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("PilierController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du pilier'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/piliers/{pilier}",
     *     tags={"Piliers"},
     *     summary="Détails d'un pilier par ID",
     *     description="Récupère les détails d'un pilier via son ID.",
     *     @OA\Parameter(
     *         name="pilier",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pilier trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Pilier"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/piliers/slug/{slug}",
     *     tags={"Piliers"},
     *     summary="Détails d'un pilier par Slug",
     *     description="Récupère les détails d'un pilier via son slug.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pilier trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Pilier"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Pilier $pilier)
    {
        return response()->json([
            'success' => true,
            'data' => $pilier,
            'message' => 'Pilier récupéré avec succès'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/piliers/{pilier}",
     *     tags={"Piliers"},
     *     summary="Mettre à jour un pilier par ID",
     *     description="Met à jour un pilier existant via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pilier",
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
     *         description="Pilier mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Pilier"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/piliers/slug/{slug}",
     *     tags={"Piliers"},
     *     summary="Mettre à jour un pilier par Slug",
     *     description="Met à jour un pilier existant via son slug.",
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
     *         description="Pilier mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Pilier"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Pilier $pilier)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                if ($pilier->image) {
                    Storage::disk('public')->delete($pilier->image);
                }
                $validated['image'] = $request->file('image')->store('piliers', 'public');
            }
            // Ne plus modifier le slug lors de la mise à jour
            $pilier->update($validated);

            return response()->json([
                'success' => true,
                'data' => $pilier,
                'message' => 'Pilier mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PilierController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du pilier'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/piliers/{pilier}",
     *     tags={"Piliers"},
     *     summary="Supprimer un pilier par ID",
     *     description="Supprime un pilier via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pilier",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pilier supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/piliers/slug/{slug}",
     *     tags={"Piliers"},
     *     summary="Supprimer un pilier par Slug",
     *     description="Supprime un pilier via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pilier supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Pilier $pilier)
    {
        try {
            if ($pilier->image) {
                Storage::disk('public')->delete($pilier->image);
            }

            $pilier->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Pilier supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PilierController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du pilier'
            ], 500);
        }
    }
}
