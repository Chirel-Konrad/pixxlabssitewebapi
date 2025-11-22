<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogCommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/blog-comments",
     *     tags={"Blog Comments"},
     *     summary="Liste des commentaires de blog",
     *     description="Récupère tous les commentaires de blog",
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BlogComment")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $comments = BlogComment::with('blog', 'user')->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $comments,
                'message' => 'Commentaires récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('BlogCommentController@index: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des commentaires'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/blog-comments",
     *     tags={"Blog Comments"},
     *     summary="Ajouter un commentaire",
     *     description="Ajoute un commentaire à un article de blog",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"blog_id", "user_id", "comment"},
     *             @OA\Property(property="blog_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="comment", type="string", example="Super article !")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Commentaire créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/BlogComment"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'blog_id' => 'required|exists:blogs,id',
                'user_id' => 'required|exists:users,id',
                'comment' => 'required|string',
            ]);

            $comment = BlogComment::create($validated);

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Commentaire créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error('BlogCommentController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du commentaire'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/blog-comments/{blogComment}",
     *     tags={"Blog Comments"},
     *     summary="Détails d'un commentaire",
     *     description="Récupère un commentaire par ID",
     *     @OA\Parameter(
     *         name="blogComment",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commentaire trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/BlogComment"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(BlogComment $blogComment)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $blogComment->load('blog', 'user'),
                'message' => 'Commentaire récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('BlogCommentController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération du commentaire'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/blog-comments/{blogComment}",
     *     tags={"Blog Comments"},
     *     summary="Mettre à jour un commentaire",
     *     description="Met à jour un commentaire existant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="blogComment",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="comment", type="string", example="Commentaire modifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commentaire mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/BlogComment"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, BlogComment $blogComment)
    {
        try {
            $validated = $request->validate([
                'comment' => 'sometimes|string',
            ]);

            $blogComment->update($validated);

            return response()->json([
                'success' => true,
                'data' => $blogComment,
                'message' => 'Commentaire mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('BlogCommentController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du commentaire'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/blog-comments/{blogComment}",
     *     tags={"Blog Comments"},
     *     summary="Supprimer un commentaire",
     *     description="Supprime un commentaire",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="blogComment",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Commentaire supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(BlogComment $blogComment)
    {
        try {
            $blogComment->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Commentaire supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('BlogCommentController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du commentaire'
            ], 500);
        }
    }
}
