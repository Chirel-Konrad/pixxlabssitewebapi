<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebinarController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/webinars",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Webinar")),
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
            $webinars = Webinar::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $webinars,
                'message' => 'Webinaires récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("WebinarController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des webinaires'
            ], 500);
        }
    }

   /**
     * @OA\Post(
     *     path="/api/webinars",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Webinar"),
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
                "whose" => "required|string|max:255",
                "date" => "required|string|max:255",
                "time" => "required|string|max:255",
                "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
                "video" => "nullable|file|mimes:mp4,mov,avi,webm|max:51200", // max 50MB
            ]);

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

            return response()->json([
                'success' => true,
                'data' => $webinar,
                'message' => 'Webinaire créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            Log::error("WebinarController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du webinaire'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/webinars/{webinar}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Webinar"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/webinars/slug/{slug}",
     *     tags={"Webinars"},
     *     summary="Détails d'un webinaire par Slug",
     *     description="Récupère les détails d'un webinaire via son slug.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webinaire trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Webinar"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Webinar $webinar)
    {
        return response()->json([
            'success' => true,
            'data' => $webinar,
            'message' => 'Webinaire récupéré avec succès'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/webinars/{webinar}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Webinar"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/webinars/slug/{slug}",
     *     tags={"Webinars"},
     *     summary="Mettre à jour un webinaire par Slug",
     *     description="Met à jour un webinaire existant via son slug.",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Webinar"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Webinar $webinar)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "whose" => "required|string|max:255",
                "date" => "required|string|max:255",
                "time" => "required|string|max:255",
                "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
                "video" => "nullable|file|mimes:mp4,mov,avi,webm|max:51200", // max 50MB
            ]);

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

            return response()->json([
                'success' => true,
                'data' => $webinar,
                'message' => 'Webinaire mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error("WebinarController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du webinaire'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/webinars/{webinar}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/webinars/slug/{slug}",
     *     tags={"Webinars"},
     *     summary="Supprimer un webinaire par Slug",
     *     description="Supprime un webinaire via son slug.",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Webinar $webinar)
    {
        try {
            $webinar->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Webinaire supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("WebinarController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du webinaire'
            ], 500);
        }
    }
}
