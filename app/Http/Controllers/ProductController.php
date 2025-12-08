<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/products",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $status = $request->get('status');

            $query = Product::latest();

            if ($status && in_array($status, ['available', 'pending'])) {
                $query->where('status', $status);
            }

            $products = $query->paginate($perPage);

            return $this->paginatedResponse(ProductResource::collection($products), 'Produits récupérés avec succès');
        } catch (\Exception $e) {
            Log::error("ProductController@index: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la récupération des produits', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
     public function store(StoreProductRequest $request)
    {
        try {
            $validated = $request->validated();

            // Génération automatique du slug
            $validated['slug'] = Str::slug($request->name) . '-' . uniqid();

            // Upload image
            if ($request->hasFile("image")) {
                $validated['image'] = $request->file("image")->store("products", "public");
            }

            $product = Product::create($validated);

            return $this->successResponse(new ProductResource($product), 'Produit créé avec succès', 201);
        } catch (\Exception $e) {
            Log::error("ProductController@store: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la création du produit', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{product}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/products/slug/{slug}",
     *     tags={"Products"},
     *     summary="Consulter un produit via son slug (URL publique SEO‑friendly)",
     *     description="Récupère un produit par son slug URL‑friendly. À utiliser côté front pour des URLs lisibles et pour éviter d'exposer des IDs incrémentaux (anti‑énumération).",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(Product $product)
    {
        return $this->successResponse(new ProductResource($product), 'Produit récupéré avec succès');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{product}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/products/slug/{slug}",
     *     tags={"Products"},
     *     summary="Mettre à jour un produit via son slug (référence URL‑friendly)",
     *     description="Met à jour un produit en l'identifiant par son slug public, pratique quand seul l'URL publique est connue côté client.",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $validated = $request->validated();

            // Ne plus modifier le slug lors de la mise à jour

            // Upload image
            if ($request->hasFile("image")) {
                if ($product->image) {
                    Storage::disk("public")->delete($product->image);
                }
                $validated['image'] = $request->file("image")->store("products", "public");
            }

            $product->update($validated);

            return $this->successResponse(new ProductResource($product), 'Produit mis à jour avec succès');
        } catch (\Exception $e) {
            Log::error("ProductController@update: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la mise à jour du produit', 500, $e->getMessage());
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/products/{product}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/v1/products/slug/{slug}",
     *     tags={"Products"},
     *     summary="Supprimer un produit via son slug (URL publique)",
     *     description="Supprime un produit en le ciblant via son slug public, sans exposer l'ID interne.",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
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

            return $this->successResponse(null, 'Produit supprimé avec succès');
        } catch (\Exception $e) {
            Log::error("ProductController@destroy: " . $e->getMessage());
            return $this->errorResponse('Erreur lors de la suppression du produit', 500, $e->getMessage());
        }
    }
}
