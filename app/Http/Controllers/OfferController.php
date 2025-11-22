<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/offers",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Offer")),
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
            $offers = Offer::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $offers,
                'message' => 'Offres récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("OfferController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des offres'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/offers",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Offer"),
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
            ]);

            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            $offer = Offer::create($validated);

            return response()->json([
                'success' => true,
                'data' => $offer,
                'message' => 'Offre créée avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("OfferController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création de l\'offre'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/offers/{offer}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Offer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/offers/slug/{slug}",
     *     tags={"Offers"},
     *     summary="Détails d'une offre par Slug",
     *     description="Récupère les détails d'une offre via son slug.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Offre trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Offer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Offer $offer)
    {
        return response()->json([
            'success' => true,
            'data' => $offer,
            'message' => 'Offre récupérée avec succès'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/offers/{offer}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Offer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/offers/slug/{slug}",
     *     tags={"Offers"},
     *     summary="Mettre à jour une offre par Slug",
     *     description="Met à jour une offre existante via son slug.",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Offer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Offer $offer)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
            ]);
            // Ne plus modifier le slug lors de la mise à jour


            $offer->update($validated);

            return response()->json([
                'success' => true,
                'data' => $offer,
                'message' => 'Offre mise à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("OfferController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour de l\'offre'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/offers/{offer}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/offers/slug/{slug}",
     *     tags={"Offers"},
     *     summary="Supprimer une offre par Slug",
     *     description="Supprime une offre via son slug.",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Offer $offer)
    {
        try {
            $offer->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Offre supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("OfferController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de l\'offre'
            ], 500);
        }
    }
}
