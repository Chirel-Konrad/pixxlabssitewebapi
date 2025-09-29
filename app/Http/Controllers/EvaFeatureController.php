<?php

namespace App\Http\Controllers;

use App\Models\EvaFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EvaFeatureController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $features = EvaFeature::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $features,
                'message' => 'Fonctionnalités récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des fonctionnalités'
            ], 500);
        }
    }



public function store(Request $request)
{
    try {
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "required|string",
            "logo" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
        ]);

        // Génération automatique du slug unique
        $validated['slug'] = Str::slug($request->title) . '-' . uniqid();

        // Upload du logo si présent
        if ($request->hasFile("logo")) {
            $validated['logo'] = $request->file("logo")->store("eva_features", "public");
        }

        $feature = EvaFeature::create($validated);

        return response()->json([
            'success' => true,
            'data' => $feature,
            'message' => 'Fonctionnalité créée avec succès'
        ], 201);
    } catch (\Exception $e) {
        Log::error("EvaFeatureController@store: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de la création de la fonctionnalité'
        ], 500);
    }
}

    public function show(EvaFeature $evaFeature)
    {
        return response()->json([
            'success' => true,
            'data' => $evaFeature,
            'message' => 'Fonctionnalité récupérée avec succès'
        ]);
    }

    public function update(Request $request, EvaFeature $evaFeature)
{
    try {
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "required|string",
            "logo" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
        ]);

        // Ne plus modifier le slug lors de la mise à jour

        if ($request->hasFile("logo")) {
            if ($evaFeature->logo) {
                Storage::disk("public")->delete($evaFeature->logo);
            }
            $validated['logo'] = $request->file("logo")->store("eva_features", "public");
        }

        $evaFeature->update($validated);

        return response()->json([
            'success' => true,
            'data' => $evaFeature,
            'message' => 'Fonctionnalité mise à jour avec succès'
        ]);
    } catch (\Exception $e) {
        Log::error("EvaFeatureController@update: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de la mise à jour de la fonctionnalité'
        ], 500);
    }
}


    public function destroy(EvaFeature $evaFeature)
    {
        try {
            if ($evaFeature->logo) {
                Storage::disk("public")->delete($evaFeature->logo);
            }
            $evaFeature->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Fonctionnalité supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("EvaFeatureController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de la fonctionnalité'
            ], 500);
        }
    }
}
