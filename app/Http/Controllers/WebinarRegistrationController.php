<?php

namespace App\Http\Controllers;

use App\Models\WebinarRegistration;
use App\Http\Resources\WebinarRegistrationResource;
use App\Http\Requests\StoreWebinarRegistrationRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebinarRegistrationController extends Controller
{
    use ApiResponse;

    // Liste des inscriptions de l'utilisateur connecté
    /**
     * @OA\Get(
     *     path="/api/webinar-registrations",
     *     tags={"Webinar Registrations"},
     *     summary="Mes inscriptions aux webinaires",
     *     description="Récupère la liste des inscriptions de l'utilisateur connecté",
     *     security={{"bearerAuth":{}}},
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

            $registrations = WebinarRegistration::with('webinar')
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate($perPage);

            return $this->paginatedResponse(WebinarRegistrationResource::collection($registrations), 'Vos inscriptions récupérées avec succès');
        } catch (\Exception $e) {
            Log::error("WebinarRegistrationController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération de vos inscriptions', 500, $e->getMessage());
        }
    }

    // S'inscrire à un webinaire
    /**
     * @OA\Post(
     *     path="/api/webinar-registrations",
     *     tags={"Webinar Registrations"},
     *     summary="S'inscrire à un webinaire",
     *     description="Inscrit l'utilisateur connecté à un webinaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"webinar_id"},
     *             @OA\Property(property="webinar_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreWebinarRegistrationRequest $request)
    {
        try {
            $validated = $request->validated();

            // Génération automatique du slug unique
            // Génération automatique du slug unique
            $slug = Str::slug(auth()->id() . '-' . $validated['webinar_id']) . '-' . uniqid();

            $registration = WebinarRegistration::create([
                'user_id' => auth()->id(),
                'webinar_id' => $validated['webinar_id'],
                'slug' => $slug,
            ]);

            return $this->successResponse(new WebinarRegistrationResource($registration->load('webinar')), 'Inscription créée avec succès', 201);
        } catch (\Exception $e) {
            Log::error("WebinarRegistrationController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création de l\'inscription', 500, $e->getMessage());
        }
    }

    // Détails d'une inscription
    /**
     * @OA\Get(
     *     path="/api/webinar-registrations/{webinarRegistration}",
     *     tags={"Webinar Registrations"},
     *     summary="Détails d'une inscription par ID",
     *     description="Récupère les détails d'une inscription via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="webinarRegistration",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/webinar-registrations/slug/{slug}",
     *     tags={"Webinar Registrations"},
     *     summary="Détails d'une inscription par Slug",
     *     description="Récupère les détails d'une inscription via son slug. Cette route est recommandée pour les URL publiques (SEO friendly) et la sécurité, préférée à l'ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(WebinarRegistration $webinarRegistration)
    {
        try {
             if ($webinarRegistration->user_id !== auth()->id()) {
                 return $this->errorResponse('Non autorisé', 403);
             }
             return $this->successResponse(new WebinarRegistrationResource($webinarRegistration->load('webinar')), 'Inscription récupérée avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération de l\'inscription', 500, $e->getMessage());
        }
    }

    // Se désinscrire d'un webinaire
    /**
     * @OA\Delete(
     *     path="/api/webinar-registrations/{webinarRegistration}",
     *     tags={"Webinar Registrations"},
     *     summary="Supprimer une inscription par ID",
     *     description="Supprime une inscription via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="webinarRegistration",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription supprimée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/webinar-registrations/slug/{slug}",
     *     tags={"Webinar Registrations"},
     *     summary="Supprimer une inscription par Slug",
     *     description="Supprime une inscription via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription supprimée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function destroy(WebinarRegistration $webinarRegistration)
    {
        try {
            // Vérifie que c'est l'utilisateur connecté
            if ($webinarRegistration->user_id !== auth()->id()) {
                return $this->errorResponse('Vous ne pouvez pas supprimer cette inscription', 403);
            }

            $webinarRegistration->delete();

            return $this->successResponse(null, 'Inscription annulée avec succès');
        } catch (\Exception $e) {
            Log::error("WebinarRegistrationController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression de l\'inscription', 500, $e->getMessage());
        }
    }
}
