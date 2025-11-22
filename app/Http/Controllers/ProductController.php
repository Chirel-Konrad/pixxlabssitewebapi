<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Liste des produits",
     *     description="Récupère la liste paginée des produits",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filtrer par statut (available, pending)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"available", "pending"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
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
            $perPage = $request->get('per_page', 10);
            $status = $request->get('status'); // ✅ récupère le filtre depuis l'URL

            $query = Product::latest();

            // ✅ Ajout du filtre
            if ($status && in_array($status, ['available', 'pending'])) {
                $query->where('status', $status);
            }

            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Produits récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("ProductController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des produits'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Créer un produit",
     *     description="Crée un nouveau produit",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "price"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="status", type="string", enum={"available", "pending"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Produit créé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
     public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "name" => "required|string|max:255",
                "description" => "nullable|string",
                "price" => "required|numeric",
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
                "status" => "in:available,pending"
            ]);

            // Génération automatique du slug
            $validated['slug'] = Str::slug($request->name) . '-' . uniqid();

            // Upload image
            if ($request->hasFile("image")) {
                $validated['image'] = $request->file("image")->store("products", "public");
            }

            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Produit créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("ProductController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du produit'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Détails d'un produit par ID",
     *     description="Récupère les détails d'un produit via son identifiant numérique unique. Utile pour les opérations internes ou d'administration.",
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="ID unique du produit",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/products/slug/{slug}",
     *     tags={"Products"},
     *     summary="Détails d'un produit par Slug",
     *     description="Récupère les détails d'un produit via son slug (chaîne URL-friendly). Cette méthode est recommandée pour le frontend public car elle améliore le SEO et masque l'ID interne.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Slug du produit (ex: mon-super-produit)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Produit récupéré avec succès'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Mettre à jour un produit par ID",
     *     description="Met à jour un produit existant via son ID. Permet de modifier les informations sans changer l'URL publique (slug).",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="status", type="string", enum={"available", "pending"}),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/products/slug/{slug}",
     *     tags={"Products"},
     *     summary="Mettre à jour un produit par Slug",
     *     description="Met à jour un produit en l'identifiant par son slug. Utile si le client ne connaît que l'URL publique du produit.",
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
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="image", type="string", format="binary"),
     *                 @OA\Property(property="status", type="string", enum={"available", "pending"}),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit mis à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Product"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                "name" => "required|string|max:255",
                "description" => "nullable|string",
                "price" => "required|numeric",
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048",
                "status" => "in:available,pending"
            ]);

            // Ne plus modifier le slug lors de la mise à jour

            // Upload image
            if ($request->hasFile("image")) {
                if ($product->image) {
                    Storage::disk("public")->delete($product->image);
                }
                $validated['image'] = $request->file("image")->store("products", "public");
            }

            $product->update($validated);

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Produit mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("ProductController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du produit'
            ], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/products/{product}",
     *     tags={"Products"},
     *     summary="Supprimer un produit par ID",
     *     description="Supprime définitivement un produit via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/products/slug/{slug}",
     *     tags={"Products"},
     *     summary="Supprimer un produit par Slug",
     *     description="Supprime définitivement un produit via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit supprimé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image) {
                Storage::disk("public")->delete($product->image);
            }
            $product->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Produit supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("ProductController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du produit'
            ], 500);
        }
    }
}
