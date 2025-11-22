<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestimonialController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/testimonials",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Testimonial")),
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
            $testimonials = Testimonial::with('user')->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $testimonials,
                'message' => 'Témoignages récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("TestimonialController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des témoignages'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/testimonials",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimonial"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "user_id" => "nullable|exists:users,id",
                "content" => "required|string",
            ]);
        // Génération du slug unique
        $validated['slug'] = Str::slug(substr($validated['content'], 0, 50)) . '-' . uniqid();

            $testimonial = Testimonial::create($validated);

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Témoignage créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("TestimonialController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du témoignage'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/testimonials/{testimonial}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimonial"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/testimonials/slug/{slug}",
     *     tags={"Testimonials"},
     *     summary="Détails d'un témoignage par Slug",
     *     description="Récupère les détails d'un témoignage via son slug.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Témoignage trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimonial"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Testimonial $testimonial)
    {
        return response()->json([
            'success' => true,
            'data' => $testimonial,
            'message' => 'Témoignage récupéré avec succès'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/testimonials/{testimonial}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimonial"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/testimonials/slug/{slug}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimonial"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        try {
            $validated = $request->validate([
                "user_id" => "nullable|exists:users,id",
                "content" => "required|string",
            ]);
           // Ne plus modifier le slug lors de la mise à jour
            $testimonial->update($validated);

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Témoignage mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("TestimonialController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du témoignage'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/testimonials/{testimonial}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/testimonials/slug/{slug}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Testimonial $testimonial)
    {
        try {
            $testimonial->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Témoignage supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("TestimonialController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du témoignage'
            ], 500);
        }
    }
}
