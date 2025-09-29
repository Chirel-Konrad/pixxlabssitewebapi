<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $newsletters = Newsletter::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $newsletters,
                'message' => 'Newsletters récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur NewsletterController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des newsletters'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletters,email|max:255',
        ]);

        // Génération d'un slug unique basé sur l'email
        $validated['slug'] = Str::slug(explode('@', $validated['email'])[0]) . '-' . uniqid();

        $newsletter = Newsletter::create($validated);

        return response()->json([
            'success' => true,
            'data' => $newsletter,
            'message' => 'Inscription à la newsletter réussie'
        ], 201);
    } catch (\Exception $e) {
        Log::error("Erreur NewsletterController@store: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de l’inscription à la newsletter'
        ], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(Newsletter $newsletter)
    {
        return response()->json([
            'success' => true,
            'data' => $newsletter,
            'message' => 'Newsletter récupérée avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, Newsletter $newsletter)
{
    try {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletters,email,' . $newsletter->id . ',id|max:255',
        ]);

        // Ne plus modifier le slug lors de la mise à jour

        $newsletter->update($validated);

        return response()->json([
            'success' => true,
            'data' => $newsletter,
            'message' => 'Newsletter mise à jour avec succès'
        ]);
    } catch (\Exception $e) {
        Log::error("Erreur NewsletterController@update: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Erreur lors de la mise à jour de la newsletter'
        ], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Newsletter $newsletter)
    {
        try {
            $newsletter->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Newsletter supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur NewsletterController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression de la newsletter'
            ], 500);
        }
    }
}
