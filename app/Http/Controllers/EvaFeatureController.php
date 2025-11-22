<?php

namespace App\Http\Controllers;

use App\Models\EvaFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EvaFeatureController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/eva-features",
     *     tags={"Eva Features"},
     *     summary="Liste des fonctionnalités Eva",
     *     description="Récupère la liste paginée des fonctionnalités",
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
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EvaFeature")),
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
            $features = EvaFeature::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $features,
                'message' => 'Fonctionnalités récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des fonctionnalités'
            ], 500);
        }
    }



/**
     * @OA\Post(
     *     path="/api/eva-features",
     *     tags={"Eva Features"},
     *     summary="Créer une fonctionnalité",
     *     description="Crée une nouvelle fonctionnalité Eva",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description"},
     *                 @OA\Property(property="title", type="string", example="Feature 1"),
     *                 @OA\Property(property="description", type="string", example="Description..."),
     *                 @OA\Property(property="logo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Fonctionnalité créée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EvaFeature"),
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
            "description" => "required|string",
            "logo" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
        ]);

        // Génération automatique du slug unique
        $validated['slug'] = Str::slug($request->title) . '-' . uniqid();

        // Upload du logo si présent
        if ($request->hasFile("logo")) {
            $validated['logo'] = $request->file("logo")->store("eva_features", "public");
        }

        $feature = EvaFeature::create($validated);

        return response()->json([
            'success' => true,
            'data' => $feature,
            'message' => 'Fonctionnalité créée avec succès'
        ], 201);
    } catch (\Exception $e) {
        Log::error("EvaFeatureController@store: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de la création de la fonctionnalité'
        ], 500);
    }
}

    /**
     * @OA\Get(
     *     path="/api/eva-features/{evaFeature}",
     *     tags={"Eva Features"},
     *     summary="Détails d'une fonctionnalité par ID",
     *     description="Récupère les détails d'une fonctionnalité via son ID.",
     *     @OA\Parameter(
     *         name="evaFeature",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fonctionnalité trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EvaFeature"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/eva-features/slug/{slug}",
     *     tags={"Eva Features"},
     *     summary="Détails d'une fonctionnalité par Slug",
     *     description="Récupère les détails d'une fonctionnalité via son slug.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fonctionnalité trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EvaFeature"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(EvaFeature $evaFeature)
    {
        return response()->json([
            'success' => true,
            'data' => $evaFeature,
            'message' => 'Fonctionnalité récupérée avec succès'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/eva-features/{evaFeature}",
     *     tags={"Eva Features"},
     *     summary="Mettre à jour une fonctionnalité par ID",
     *     description="Met à jour une fonctionnalité existante via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="evaFeature",
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
     *                 @OA\Property(property="logo", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fonctionnalité mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EvaFeature"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/eva-features/slug/{slug}",
     *     tags={"Eva Features"},
     *     summary="Mettre à jour une fonctionnalité par Slug",
     *     description="Met à jour une fonctionnalité existante via son slug.",
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
     *                 @OA\Property(property="logo", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fonctionnalité mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/EvaFeature"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, EvaFeature $evaFeature)
{
    try {
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "required|string",
            "logo" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
        ]);

        // Ne plus modifier le slug lors de la mise à jour

        if ($request->hasFile("logo")) {
            if ($evaFeature->logo) {
                Storage::disk("public")->delete($evaFeature->logo);
            }
            $validated['logo'] = $request->file("logo")->store("eva_features", "public");
        }

        $evaFeature->update($validated);

        return response()->json([
            'success' => true,
            'data' => $evaFeature,
            'message' => 'Fonctionnalité mise à jour avec succès'
        ]);
    } catch (\Exception $e) {
        Log::error("EvaFeatureController@update: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de la mise à jour de la fonctionnalité'
        ], 500);
    }
}


    /**
     * @OA\Delete(
     *     path="/api/eva-features/{evaFeature}",
     *     tags={"Eva Features"},
     *     summary="Supprimer une fonctionnalité par ID",
     *     description="Supprime une fonctionnalité via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="evaFeature",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fonctionnalité supprimée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/eva-features/slug/{slug}",
     *     tags={"Eva Features"},
     *     summary="Supprimer une fonctionnalité par Slug",
     *     description="Supprime une fonctionnalité via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fonctionnalité supprimée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(EvaFeature $evaFeature)
    {
        try {
            if ($evaFeature->logo) {
                Storage::disk("public")->delete($evaFeature->logo);
            }
            $evaFeature->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Fonctionnalité supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de la fonctionnalité'
            ], 500);
        }
    }
}
