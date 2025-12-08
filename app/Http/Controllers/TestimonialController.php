<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Http\Resources\TestimonialResource;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestimonialController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/testimonials",
     *     tags={"Testimonials"},
     *     summary="Liste des témoignages",
     *     description="Récupère la liste paginée des témoignages",
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
            $testimonials = Testimonial::with('user')->latest()->paginate($perPage);

            return $this->paginatedResponse(TestimonialResource::collection($testimonials), 'Témoignages récupérés avec succès');
        } catch (\Exception $e) {
            Log::error("TestimonialController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des témoignages', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/testimonials",
     *     tags={"Testimonials"},
     *     summary="Ajouter un témoignage",
     *     description="Ajoute un nouveau témoignage",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="content", type="string", example="Super service !")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Témoignage créé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreTestimonialRequest $request)
    {
        try {
            $validated = $request->validated();
        // Génération du slug unique
        $validated['slug'] = Str::slug(substr($validated['content'], 0, 50)) . '-' . uniqid();

            $testimonial = Testimonial::create($validated);

            return $this->successResponse(new TestimonialResource($testimonial), 'Témoignage créé avec succès', 201);
        } catch (\Exception $e) {
            Log::error("TestimonialController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création du témoignage', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/testimonials/{testimonial}",
     *     tags={"Testimonials"},
     *     summary="Détails d'un témoignage par ID",
     *     description="Récupère les détails d'un témoignage via son ID.",
     *     @OA\Parameter(
     *         name="testimonial",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Témoignage trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/testimonials/slug/{slug}",
     *     tags={"Testimonials"},
     *     summary="Détails d'un témoignage par Slug",
     *     description="Récupère les détails d'un témoignage via son slug. Cette route est recommandée pour les URL publiques (SEO friendly) et la sécurité, préférée à l'ID.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Témoignage trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(Testimonial $testimonial)
    {
        return $this->successResponse(new TestimonialResource($testimonial), 'Témoignage récupéré avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/testimonials/{testimonial}",
     *     tags={"Testimonials"},
     *     summary="Mettre à jour un témoignage par ID",
     *     description="Met à jour un témoignage existant via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="testimonial",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Témoignage modifié"),
     *             @OA\Property(property="rating", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Témoignage mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/testimonials/slug/{slug}",
     *     tags={"Testimonials"},
     *     summary="Mettre à jour un témoignage par Slug",
     *     description="Met à jour un témoignage existant via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Témoignage modifié"),
     *             @OA\Property(property="rating", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Témoignage mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial)
    {
        try {
            $validated = $request->validated();
           // Ne plus modifier le slug lors de la mise à jour
            $testimonial->update($validated);

            return $this->successResponse(new TestimonialResource($testimonial), 'Témoignage mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error("TestimonialController@update: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour du témoignage', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/testimonials/{testimonial}",
     *     tags={"Testimonials"},
     *     summary="Supprimer un témoignage par ID",
     *     description="Supprime un témoignage via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="testimonial",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Témoignage supprimé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/v1/testimonials/slug/{slug}",
     *     tags={"Testimonials"},
     *     summary="Supprimer un témoignage par Slug",
     *     description="Supprime un témoignage via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Témoignage supprimé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function destroy(Testimonial $testimonial)
    {
        try {
            $testimonial->delete();
            return $this->successResponse(null, 'Témoignage supprimé avec succès');
        } catch (\Exception $e) {
            Log::error("TestimonialController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression du témoignage', 500, $e->getMessage());
        }
    }
}
