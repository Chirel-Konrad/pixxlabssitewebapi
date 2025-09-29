<?php

namespace App\Http\Controllers;

use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogCommentController extends Controller
{
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
