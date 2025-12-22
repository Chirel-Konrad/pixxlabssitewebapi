<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
use App\Http\Resources\WebinarResource;
use App\Http\Requests\StoreWebinarRequest;
use App\Http\Requests\UpdateWebinarRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebinarController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/webinars",
     *     tags={"Webinars"},
     *     summary="Liste des webinaires",
     *     description="Récupère la liste paginée des webinaires",
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
            $webinars = Webinar::latest()->paginate($perPage);

            return $this->paginatedResponse(WebinarResource::collection($webinars), 'Webinaires récupérés avec succès');
        } catch (\Exception $e) {
            Log::error("WebinarController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des webinaires', 500, $e->getMessage());
        }
    }

   /**
     * @OA\Post(
     *     path="/api/v1/admin/webinars",
     *     tags={"Webinars"},
     *     summary="Créer un webinaire",
     *     description="Crée un nouveau webinaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "whose", "date", "time"},
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="whose", type="string", example="John Doe"),
     *                 @OA\Property(property="date", type="string", example="2024-12-31"),
     *                 @OA\Property(property="time", type="string", example="14:00"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="video", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Webinaire créé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreWebinarRequest $request)
    {
        try {
            $validated = $request->validated();

            // Génération automatique du slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            // Upload de l'image si présente
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')
                    ->storeAs('uploads/webinars/images', time().'_'.$request->file('image')->getClientOriginalName(), 'public');
            }

            // Upload de la vidéo si présente
            if ($request->hasFile('video')) {
                $validated['video_url'] = $request->file('video')
                    ->storeAs('uploads/webinars/videos', time().'_'.$request->file('video')->getClientOriginalName(), 'public');
            }

            $webinar = Webinar::create($validated);

            return $this->successResponse(new WebinarResource($webinar), 'Webinaire créé avec succès', 201);

        } catch (\Exception $e) {
            Log::error("WebinarController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création du webinaire', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/webinars/{webinar}",
     *     tags={"Webinars"},
     *     summary="Détails d'un webinaire par ID",
     *     description="Récupère les détails d'un webinaire via son ID.",
     *     @OA\Parameter(
     *         name="webinar",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webinaire trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/webinars/slug/{slug}",
     *     tags={"Webinars"},
     *     summary="Consulter un webinaire via son slug (URL publique SEO‑friendly)",
     *     description="Récupère un webinaire par son slug URL‑friendly. À utiliser côté front pour des URLs lisibles et pour éviter d'exposer des IDs incrémentaux (anti‑énumération).",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webinaire trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(Webinar $webinar)
    {
        return $this->successResponse(new WebinarResource($webinar), 'Webinaire récupéré avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/webinars/{webinar}",
     *     tags={"Webinars"},
     *     summary="Mettre à jour un webinaire par ID",
     *     description="Met à jour un webinaire existant via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="webinar",
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
     *                 @OA\Property(property="date_time", type="string", format="date-time"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="link", type="string"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webinaire mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/admin/webinars/slug/{slug}",
     *     tags={"Webinars"},
     *     summary="Mettre à jour un webinaire via son slug (référence URL‑friendly)",
     *     description="Met à jour un webinaire en l'identifiant par son slug public, pratique quand seul l'URL publique est connue côté client.",
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
     *                 @OA\Property(property="date_time", type="string", format="date-time"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="link", type="string"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webinaire mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateWebinarRequest $request, Webinar $webinar)
    {
        try {
            $validated = $request->validated();

            // Ne plus modifier le slug lors de la mise à jour

            // Upload image
            if ($request->hasFile('image')) {
                if ($webinar->image && Storage::disk('public')->exists($webinar->image)) {
                    Storage::disk('public')->delete($webinar->image);
                }
                $validated['image'] = $request->file('image')
                    ->storeAs('uploads/webinars/images', time().'_'.$request->file('image')->getClientOriginalName(), 'public');
            }

            // Upload vidéo
            if ($request->hasFile('video')) {
                if ($webinar->video_url && Storage::disk('public')->exists($webinar->video_url)) {
                    Storage::disk('public')->delete($webinar->video_url);
                }
                $validated['video_url'] = $request->file('video')
                    ->storeAs('uploads/webinars/videos', time().'_'.$request->file('video')->getClientOriginalName(), 'public');
            }

            $webinar->update($validated);

            return $this->successResponse(new WebinarResource($webinar), 'Webinaire mis à jour avec succès');

        } catch (\Exception $e) {
            Log::error("WebinarController@update: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour du webinaire', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/webinars/{webinar}",
     *     tags={"Webinars"},
     *     summary="Supprimer un webinaire par ID",
     *     description="Supprime un webinaire via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="webinar",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webinaire supprimé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/v1/admin/webinars/slug/{slug}",
     *     tags={"Webinars"},
     *     summary="Supprimer un webinaire via son slug (URL publique)",
     *     description="Supprime un webinaire en le ciblant via son slug public, sans exposer l'ID interne.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webinaire supprimé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function destroy(Webinar $webinar)
    {
        try {
            $webinar->delete();
            return $this->successResponse(null, 'Webinaire supprimé avec succès');
        } catch (\Exception $e) {
            Log::error("WebinarController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression du webinaire', 500, $e->getMessage());
        }
    }
}
