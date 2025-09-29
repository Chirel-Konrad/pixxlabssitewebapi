<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $offers = Offer::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $offers,
                'message' => 'Offres récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("OfferController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des offres'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
            ]);

            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            $offer = Offer::create($validated);

            return response()->json([
                'success' => true,
                'data' => $offer,
                'message' => 'Offre créée avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("OfferController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création de l\'offre'
            ], 500);
        }
    }

    public function show(Offer $offer)
    {
        return response()->json([
            'success' => true,
            'data' => $offer,
            'message' => 'Offre récupérée avec succès'
        ]);
    }

    public function update(Request $request, Offer $offer)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
            ]);
            // Ne plus modifier le slug lors de la mise à jour


            $offer->update($validated);

            return response()->json([
                'success' => true,
                'data' => $offer,
                'message' => 'Offre mise à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("OfferController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour de l\'offre'
            ], 500);
        }
    }

    public function destroy(Offer $offer)
    {
        try {
            $offer->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Offre supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("OfferController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de l\'offre'
            ], 500);
        }
    }
}
