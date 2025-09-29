<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
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

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Produit récupéré avec succès'
        ]);
    }

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
