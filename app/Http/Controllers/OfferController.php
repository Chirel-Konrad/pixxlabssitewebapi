<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Http\Resources\OfferResource;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Requests\UpdateOfferRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/offers",
     *     tags={"Offers"},
     *     summary="Liste des offres",
     *     description="Récupère la liste paginée des offres",
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
            $offers = Offer::latest()->paginate($perPage);

            return $this->paginatedResponse(OfferResource::collection($offers), 'Offres récupérées avec succès');
        } catch (\Exception $e) {
            Log::error("OfferController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des offres', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/offers",
     *     tags={"Offers"},
     *     summary="Créer une offre",
     *     description="Crée une nouvelle offre",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Offre d'été"),
     *             @OA\Property(property="description", type="string", example="Détails...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Offre créée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreOfferRequest $request)
    {
        try {
            $validated = $request->validated();

            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            $offer = Offer::create($validated);

            return $this->successResponse(new OfferResource($offer), 'Offre créée avec succès', 201);
        } catch (\Exception $e) {
            Log::error("OfferController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création de l\'offre', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/offers/{offer}",
     *     tags={"Offers"},
     *     summary="Détails d'une offre par ID",
     *     description="Récupère les détails d'une offre via son ID.",
     *     @OA\Parameter(
     *         name="offer",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Offre trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/offers/slug/{slug}",
     *     tags={"Offers"},
     *     summary="Consulter une offre via son slug (URL publique SEO‑friendly)",
     *     description="Récupère une offre par son slug URL‑friendly. À utiliser côté front pour des URLs lisibles et pour éviter d'exposer des IDs incrémentaux (anti‑énumération).",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Offre trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(Offer $offer)
    {
        return $this->successResponse(new OfferResource($offer), 'Offre récupérée avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/offers/{offer}",
     *     tags={"Offers"},
     *     summary="Mettre à jour une offre par ID",
     *     description="Met à jour une offre existante via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="offer",
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
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Offre mise à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/offers/slug/{slug}",
     *     tags={"Offers"},
     *     summary="Mettre à jour une offre via son slug (référence URL‑friendly)",
     *     description="Met à jour une offre en l'identifiant par son slug public, pratique quand seul l'URL publique est connue côté client.",
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
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Offre mise à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateOfferRequest $request, Offer $offer)
    {
        try {
            $validated = $request->validated();
            // Ne plus modifier le slug lors de la mise à jour


            $offer->update($validated);

            return $this->successResponse(new OfferResource($offer), 'Offre mise à jour avec succès');
        } catch (\Exception $e) {
            Log::error("OfferController@update: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour de l\'offre', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/offers/{offer}",
     *     tags={"Offers"},
     *     summary="Supprimer une offre par ID",
     *     description="Supprime une offre via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="offer",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Offre supprimée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/v1/offers/slug/{slug}",
     *     tags={"Offers"},
     *     summary="Supprimer une offre via son slug (URL publique)",
     *     description="Supprime une offre en la ciblant via son slug public, sans exposer l'ID interne.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Offre supprimée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function destroy(Offer $offer)
    {
        try {
            $offer->delete();

            return $this->successResponse(null, 'Offre supprimée avec succès');
        } catch (\Exception $e) {
            Log::error("OfferController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression de l\'offre', 500, $e->getMessage());
        }
    }
}
