<?php

namespace App\Http\Controllers;

use App\Models\Pilier;
use App\Http\Resources\PilierResource;
use App\Http\Requests\StorePilierRequest;
use App\Http\Requests\UpdatePilierRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PilierController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/piliers",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $piliers = Pilier::latest()->paginate($perPage);

            return $this->paginatedResponse(PilierResource::collection($piliers), 'Piliers récupérés avec succès');
        } catch (\Exception $e) {
            Log::error("PilierController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des piliers', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/piliers",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StorePilierRequest $request)
    {
        try {
            $validated = $request->validated();
            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('piliers', 'public');
            }

            $pilier = Pilier::create($validated);

            return $this->successResponse(new PilierResource($pilier), 'Pilier créé avec succès', 201);
        } catch (\Exception $e) {
            Log::error("PilierController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création du pilier', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/piliers/{pilier}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/piliers/slug/{slug}",
     *     tags={"Piliers"},
     *     summary="Consulter un pilier via son slug (URL publique SEO‑friendly)",
     *     description="Récupère un pilier par son slug URL‑friendly. À utiliser côté front pour des URLs lisibles et pour éviter d'exposer des IDs incrémentaux (anti‑énumération).",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pilier trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(Pilier $pilier)
    {
        return $this->successResponse(new PilierResource($pilier), 'Pilier récupéré avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/piliers/{pilier}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/admin/piliers/slug/{slug}",
     *     tags={"Piliers"},
     *     summary="Mettre à jour un pilier via son slug (référence URL‑friendly)",
     *     description="Met à jour un pilier en l'identifiant par son slug public, pratique quand seul l'URL publique est connue côté client.",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdatePilierRequest $request, Pilier $pilier)
    {
        try {
            $validated = $request->validated();

            if ($request->hasFile('image')) {
                if ($pilier->image) {
                    Storage::disk('public')->delete($pilier->image);
                }
                $validated['image'] = $request->file('image')->store('piliers', 'public');
            }
            // Ne plus modifier le slug lors de la mise à jour
            $pilier->update($validated);

            return $this->successResponse(new PilierResource($pilier), 'Pilier mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error("PilierController@update: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour du pilier', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/piliers/{pilier}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/v1/admin/piliers/slug/{slug}",
     *     tags={"Piliers"},
     *     summary="Supprimer un pilier via son slug (URL publique)",
     *     description="Supprime un pilier en le ciblant via son slug public, sans exposer l'ID interne.",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
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

            return $this->successResponse(null, 'Pilier supprimé avec succès');
        } catch (\Exception $e) {
            Log::error("PilierController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression du pilier', 500, $e->getMessage());
        }
    }
}
