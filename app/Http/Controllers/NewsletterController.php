<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    /**
     * Display a paginated listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/newsletters",
     *     tags={"Newsletters"},
     *     summary="Liste des abonnés newsletter",
     *     description="Récupère la liste paginée des abonnés",
     *     security={{"bearerAuth":{}}},
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
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Newsletter")),
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
            $newsletters = Newsletter::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $newsletters,
                'message' => 'Newsletters récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur NewsletterController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des newsletters'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/newsletters",
     *     tags={"Newsletters"},
     *     summary="S'abonner à la newsletter",
     *     description="Ajoute un email à la liste de diffusion",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Newsletter"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletters,email|max:255',
        ]);

        // Génération d'un slug unique basé sur l'email
        $validated['slug'] = Str::slug(explode('@', $validated['email'])[0]) . '-' . uniqid();

        $newsletter = Newsletter::create($validated);

        return response()->json([
            'success' => true,
            'data' => $newsletter,
            'message' => 'Inscription à la newsletter réussie'
        ], 201);
    } catch (\Exception $e) {
        Log::error("Erreur NewsletterController@store: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de l’inscription à la newsletter'
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/newsletters/{newsletter}",
     *     tags={"Newsletters"},
     *     summary="Détails d'un abonné par ID",
     *     description="Récupère un abonné via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="newsletter",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Abonné trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Newsletter"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/newsletters/slug/{slug}",
     *     tags={"Newsletters"},
     *     summary="Détails d'un abonné par Slug",
     *     description="Récupère un abonné via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Abonné trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Newsletter"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Newsletter $newsletter)
    {
        return response()->json([
            'success' => true,
            'data' => $newsletter,
            'message' => 'Newsletter récupérée avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
   /**
     * @OA\Put(
     *     path="/api/newsletters/{newsletter}",
     *     tags={"Newsletters"},
     *     summary="Mettre à jour un abonné par ID",
     *     description="Met à jour l'email d'un abonné via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="newsletter",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="new@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Abonné mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Newsletter"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/newsletters/slug/{slug}",
     *     tags={"Newsletters"},
     *     summary="Mettre à jour un abonné par Slug",
     *     description="Met à jour l'email d'un abonné via son slug.",
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
     *             @OA\Property(property="email", type="string", format="email", example="new@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Abonné mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Newsletter"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Newsletter $newsletter)
{
    try {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletters,email,' . $newsletter->id . ',id|max:255',
        ]);

        // Ne plus modifier le slug lors de la mise à jour

        $newsletter->update($validated);

        return response()->json([
            'success' => true,
            'data' => $newsletter,
            'message' => 'Newsletter mise à jour avec succès'
        ]);
    } catch (\Exception $e) {
        Log::error("Erreur NewsletterController@update: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de la mise à jour de la newsletter'
        ], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/newsletters/{newsletter}",
     *     tags={"Newsletters"},
     *     summary="Supprimer un abonné par ID",
     *     description="Supprime un abonné de la newsletter via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="newsletter",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Abonné supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/newsletters/slug/{slug}",
     *     tags={"Newsletters"},
     *     summary="Supprimer un abonné par Slug",
     *     description="Supprime un abonné de la newsletter via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Abonné supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Newsletter $newsletter)
    {
        try {
            $newsletter->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Newsletter supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur NewsletterController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de la newsletter'
            ], 500);
        }
    }
}
