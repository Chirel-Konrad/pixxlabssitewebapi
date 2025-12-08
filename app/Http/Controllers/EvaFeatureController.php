<?php

namespace App\Http\Controllers;

use App\Models\EvaFeature;
use App\Http\Resources\EvaFeatureResource;
use App\Http\Requests\StoreEvaFeatureRequest;
use App\Http\Requests\UpdateEvaFeatureRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EvaFeatureController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/eva-features",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $features = EvaFeature::latest()->paginate($perPage);

            return $this->paginatedResponse(EvaFeatureResource::collection($features), 'Fonctionnalités récupérées avec succès');
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des fonctionnalités', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/eva-features",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreEvaFeatureRequest $request)
    {
        try {
            $validated = $request->validated();

            // Génération automatique du slug unique
            $validated['slug'] = Str::slug($request->title) . '-' . uniqid();

            // Upload du logo si présent
            if ($request->hasFile("logo")) {
                $validated['logo'] = $request->file("logo")->store("eva_features", "public");
            }

            $feature = EvaFeature::create($validated);

            return $this->successResponse(new EvaFeatureResource($feature), 'Fonctionnalité créée avec succès', 201);
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création de la fonctionnalité', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/eva-features/{evaFeature}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/eva-features/slug/{slug}",
     *     tags={"Eva Features"},
     *     summary="Consulter une fonctionnalité via son slug (URL publique SEO‑friendly)",
     *     description="Récupère une fonctionnalité par son slug URL‑friendly. À utiliser côté front pour des URLs lisibles et pour éviter d'exposer des IDs incrémentaux (anti‑énumération).",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fonctionnalité trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(EvaFeature $evaFeature)
    {
        return $this->successResponse(new EvaFeatureResource($evaFeature), 'Fonctionnalité récupérée avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/eva-features/{evaFeature}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/eva-features/slug/{slug}",
     *     tags={"Eva Features"},
     *     summary="Mettre à jour une fonctionnalité via son slug (référence URL‑friendly)",
     *     description="Met à jour une fonctionnalité en l'identifiant par son slug public, pratique quand seul l'URL publique est connue côté client.",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateEvaFeatureRequest $request, EvaFeature $evaFeature)
    {
        try {
            $validated = $request->validated();

            // Ne plus modifier le slug lors de la mise à jour

            if ($request->hasFile("logo")) {
                if ($evaFeature->logo) {
                    Storage::disk("public")->delete($evaFeature->logo);
                }
                $validated['logo'] = $request->file("logo")->store("eva_features", "public");
            }

            $evaFeature->update($validated);

            return $this->successResponse(new EvaFeatureResource($evaFeature), 'Fonctionnalité mise à jour avec succès');
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@update: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour de la fonctionnalité', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/eva-features/{evaFeature}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/v1/eva-features/slug/{slug}",
     *     tags={"Eva Features"},
     *     summary="Supprimer une fonctionnalité via son slug (URL publique)",
     *     description="Supprime une fonctionnalité en la ciblant via son slug public, sans exposer l'ID interne.",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
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

            return $this->successResponse(null, 'Fonctionnalité supprimée avec succès');
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression de la fonctionnalité', 500, $e->getMessage());
        }
    }
}
