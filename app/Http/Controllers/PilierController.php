<?php

namespace App\Http\Controllers;

use App\Models\Pilier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PilierController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $piliers = Pilier::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $piliers,
                'message' => 'Piliers récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PilierController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des piliers'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('piliers', 'public');
            }

            $pilier = Pilier::create($validated);

            return response()->json([
                'success' => true,
                'data' => $pilier,
                'message' => 'Pilier créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            Log::error("PilierController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du pilier'
            ], 500);
        }
    }

    public function show(Pilier $pilier)
    {
        return response()->json([
            'success' => true,
            'data' => $pilier,
            'message' => 'Pilier récupéré avec succès'
        ]);
    }

    public function update(Request $request, Pilier $pilier)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('image')) {
                if ($pilier->image) {
                    Storage::disk('public')->delete($pilier->image);
                }
                $validated['image'] = $request->file('image')->store('piliers', 'public');
            }
            // Ne plus modifier le slug lors de la mise à jour
            $pilier->update($validated);

            return response()->json([
                'success' => true,
                'data' => $pilier,
                'message' => 'Pilier mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PilierController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du pilier'
            ], 500);
        }
    }

    public function destroy(Pilier $pilier)
    {
        try {
            if ($pilier->image) {
                Storage::disk('public')->delete($pilier->image);
            }

            $pilier->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Pilier supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PilierController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du pilier'
            ], 500);
        }
    }
}
