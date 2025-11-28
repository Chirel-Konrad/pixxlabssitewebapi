<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
use App\Http\Resources\BlogCommentResource;
use App\Http\Requests\StoreBlogCommentRequest;
use App\Http\Requests\UpdateBlogCommentRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogCommentController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/blog-comments",
     *     tags={"Blog Comments"},
     *     summary="Liste des commentaires de blog",
     *     description="Récupère tous les commentaires de blog",
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function index()
    {
        try {
            $comments = BlogComment::with('blog', 'user')->latest()->get();

            return $this->successResponse(BlogCommentResource::collection($comments), 'Commentaires récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('BlogCommentController@index: ' . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des commentaires', 500, $e->getMessage());
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreBlogCommentRequest $request)
    {
        try {
            $validated = $request->validated();

            $comment = BlogComment::create($validated);

            return $this->successResponse(new BlogCommentResource($comment), 'Commentaire créé avec succès', 201);
        } catch (\Exception $e) {
            Log::error('BlogCommentController@store: ' . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création du commentaire', 500, $e->getMessage());
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(BlogComment $blogComment)
    {
        try {
            return $this->successResponse(new BlogCommentResource($blogComment->load('blog', 'user')), 'Commentaire récupéré avec succès');
        } catch (\Exception $e) {
            Log::error('BlogCommentController@show: ' . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération du commentaire', 500, $e->getMessage());
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateBlogCommentRequest $request, BlogComment $blogComment)
    {
        try {
            $validated = $request->validated();

            $blogComment->update($validated);

            return $this->successResponse(new BlogCommentResource($blogComment), 'Commentaire mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error('BlogCommentController@update: ' . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour du commentaire', 500, $e->getMessage());
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function destroy(BlogComment $blogComment)
    {
        try {
            $blogComment->delete();

            return $this->successResponse(null, 'Commentaire supprimé avec succès');
        } catch (\Exception $e) {
            Log::error('BlogCommentController@destroy: ' . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression du commentaire', 500, $e->getMessage());
        }
    }
}
