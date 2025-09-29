<?php

namespace App\Http\Controllers;

use App\Models\WebinarRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebinarRegistrationController extends Controller
{
    // Liste des inscriptions de l'utilisateur connecté
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);

            $registrations = WebinarRegistration::with('webinar')
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $registrations,
                'message' => 'Vos inscriptions récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("WebinarRegistrationController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération de vos inscriptions'
            ], 500);
        }
    }

    // S'inscrire à un webinaire
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "webinar_id" => "required|exists:webinars,id",
            ]);

            // Vérifie si l'utilisateur est déjà inscrit
            $exists = WebinarRegistration::where('user_id', auth()->id())
                ->where('webinar_id', $validated['webinar_id'])
                ->first();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous êtes déjà inscrit à ce webinaire'
                ], 409);
            }
            // Génération automatique du slug unique
            $slug = Str::slug(auth()->id() . '-' . $validated['webinar_id']) . '-' . uniqid();

            $registration = WebinarRegistration::create([
                'user_id' => auth()->id(),
                'webinar_id' => $validated['webinar_id'],
                'slug' => $slug,
            ]);

            return response()->json([
                'success' => true,
                'data' => $registration->load('webinar'),
                'message' => 'Inscription créée avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("WebinarRegistrationController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création de l\'inscription'
            ], 500);
        }
    }

    // Se désinscrire d'un webinaire
    public function destroy(WebinarRegistration $webinarRegistration)
    {
        try {
            // Vérifie que c'est l'utilisateur connecté
            if ($webinarRegistration->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer cette inscription'
                ], 403);
            }

            $webinarRegistration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inscription annulée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("WebinarRegistrationController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de l\'inscription'
            ], 500);
        }
    }
}
