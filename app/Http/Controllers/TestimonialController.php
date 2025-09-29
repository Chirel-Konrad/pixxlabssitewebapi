<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $testimonials = Testimonial::with('user')->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $testimonials,
                'message' => 'Témoignages récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("TestimonialController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des témoignages'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "user_id" => "nullable|exists:users,id",
                "content" => "required|string",
            ]);
        // Génération du slug unique
        $validated['slug'] = Str::slug(substr($validated['content'], 0, 50)) . '-' . uniqid();

            $testimonial = Testimonial::create($validated);

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Témoignage créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("TestimonialController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du témoignage'
            ], 500);
        }
    }

    public function show(Testimonial $testimonial)
    {
        return response()->json([
            'success' => true,
            'data' => $testimonial,
            'message' => 'Témoignage récupéré avec succès'
        ]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        try {
            $validated = $request->validate([
                "user_id" => "nullable|exists:users,id",
                "content" => "required|string",
            ]);
           // Ne plus modifier le slug lors de la mise à jour
            $testimonial->update($validated);

            return response()->json([
                'success' => true,
                'data' => $testimonial,
                'message' => 'Témoignage mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("TestimonialController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du témoignage'
            ], 500);
        }
    }

    public function destroy(Testimonial $testimonial)
    {
        try {
            $testimonial->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Témoignage supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("TestimonialController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du témoignage'
            ], 500);
        }
    }
}
