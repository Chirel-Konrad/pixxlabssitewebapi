<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/blogs",
     *     tags={"Blogs"},
     *     summary="Liste de tous les articles de blog",
     *     description="Récupère la liste paginée de tous les articles de blog avec leurs auteurs et commentaires",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Blog")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="last_page", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Erreur lors de la récupération des articles"),
     *             @OA\Property(property="message", type="string", example="Database connection error")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $blogs = Blog::with('user', 'comments')->latest()->paginate(10);
            return response()->json(['success' => true, 'data' => $blogs]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des articles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/blogs",
     *     tags={"Blogs"},
     *     summary="Créer un nouvel article de blog",
     *     description="Crée un nouvel article de blog avec upload d'image optionnel. L'utilisateur doit être authentifié.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content", "category"},
     *                 @OA\Property(property="title", type="string", maxLength=255, example="Comment devenir développeur en 2024", description="Titre de l'article"),
     *                 @OA\Property(property="content", type="string", example="Contenu complet de l'article...", description="Contenu de l'article en HTML ou texte"),
     *                 @OA\Property(
     *                     property="category",
     *                     type="string",
     *                     enum={"Action", "Développement personnel", "Technologie", "Business", "Santé", "Lifestyle", "Éducation", "Divertissement", "Culture", "Voyage"},
     *                     example="Technologie",
     *                     description="Catégorie de l'article"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image de couverture (JPG, JPEG, PNG - Max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Article créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Blog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Erreur lors de la création de l'article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $categories = ['Action', 'Développement personnel', 'Technologie', 'Business', 'Santé', 'Lifestyle', 'Éducation', 'Divertissement', 'Culture', 'Voyage'];

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'category' => 'required|string|in:' . implode(',', $categories),
            ]);

            $validated['user_id'] = auth()->id();
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')
                    ->storeAs('uploads/blogs', time() . '_' . $request->file('image')->getClientOriginalName(), 'public');
            }

            $blog = Blog::create($validated);

            return response()->json(['success' => true, 'data' => $blog], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Erreur lors de la création de l'article",
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/blogs/{blog}",
     *     tags={"Blogs"},
     *     summary="Détails d'un article de blog par ID",
     *     description="Récupère les détails complets d'un article avec son auteur et ses commentaires",
     *     @OA\Parameter(
     *         name="blog",
     *         in="path",
     *         description="ID de l'article",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Blog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Blog]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Erreur lors de la récupération de l'article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/blogs/slug/{slug}",
     *     tags={"Blogs"},
     *     summary="Détails d'un article de blog par slug",
     *     description="Récupère les détails complets d'un article via son slug avec auteur et commentaires",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Slug de l'article (URL-friendly)",
     *         required=true,
     *         @OA\Schema(type="string", example="comment-devenir-developpeur-abc123")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Blog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Blog]")
     *         )
     *     )
     * )
     */
    public function show(Blog $blog)
    {
        try {
            return response()->json(['success' => true, 'data' => $blog->load('user', 'comments')]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Erreur lors de la récupération de l'article",
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/blogs/{blog}",
     *     tags={"Blogs"},
     *     summary="Mettre à jour un article de blog par ID",
     *     description="Met à jour un article existant. Tous les champs sont optionnels. L'image peut être remplacée.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="blog",
     *         in="path",
     *         description="ID de l'article à modifier",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", maxLength=255, example="Titre modifié"),
     *                 @OA\Property(property="content", type="string", example="Contenu modifié..."),
     *                 @OA\Property(
     *                     property="category",
     *                     type="string",
     *                     enum={"Action", "Développement personnel", "Technologie", "Business", "Santé", "Lifestyle", "Éducation", "Divertissement", "Culture", "Voyage"},
     *                     example="Business"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Nouvelle image (remplace l'ancienne si fournie)"
     *                 ),
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     example="PUT",
     *                     description="Requis pour le multipart/form-data"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Blog")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Blog]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/blogs/slug/{slug}",
     *     tags={"Blogs"},
     *     summary="Mettre à jour un article de blog par slug",
     *     description="Met à jour un article existant via son slug",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Slug de l'article",
     *         required=true,
     *         @OA\Schema(type="string", example="comment-devenir-developpeur-abc123")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="category", type="string"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Blog")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Blog $blog)
    {
        try {
            $categories = ['Action', 'Développement personnel', 'Technologie', 'Business', 'Santé', 'Lifestyle', 'Éducation', 'Divertissement', 'Culture', 'Voyage'];

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'category' => 'sometimes|string|in:' . implode(',', $categories),
            ]);

            if ($request->hasFile('image')) {
                if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                    Storage::disk('public')->delete($blog->image);
                }
                $validated['image'] = $request->file('image')
                    ->storeAs('uploads/blogs', time() . '_' . $request->file('image')->getClientOriginalName(), 'public');
            }

            $blog->update($validated);

            return response()->json(['success' => true, 'data' => $blog]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Erreur lors de la mise à jour de l'article",
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/blogs/{blog}",
     *     tags={"Blogs"},
     *     summary="Supprimer un article de blog par ID",
     *     description="Supprime définitivement un article et son image associée du stockage",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="blog",
     *         in="path",
     *         description="ID de l'article à supprimer",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Article supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Blog]")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Erreur lors de la suppression de l'article")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/blogs/slug/{slug}",
     *     tags={"Blogs"},
     *     summary="Supprimer un article de blog par slug",
     *     description="Supprime définitivement un article via son slug",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Slug de l'article",
     *         required=true,
     *         @OA\Schema(type="string", example="comment-devenir-developpeur-abc123")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Article supprimé avec succès")
     *         )
     *     )
     * )
     */
    public function destroy(Blog $blog)
    {
        try {
            if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                Storage::disk('public')->delete($blog->image);
            }
            $blog->delete();
            return response()->json(['success' => true, 'message' => 'Article supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Erreur lors de la suppression de l'article",
                'message' => $e->getMessage()
            ], 500);
        }
    }
}