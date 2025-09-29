<?php

namespace App\Http\Controllers;

use App\Models\Privilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrivilegeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $privileges = Privilege::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $privileges,
                'message' => 'Privilèges récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des privilèges'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
            ]);

            // Slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
            if ($request->hasFile("image")) {
                $validated['image'] = $request->file("image")->store("privileges", "public");
            }

            $privilege = Privilege::create($validated);

            return response()->json([
                'success' => true,
                'data' => $privilege,
                'message' => 'Privilège créé avec succès'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du privilège'
            ], 500);
        }
    }

    public function show(Privilege $privilege)
    {
        return response()->json([
            'success' => true,
            'data' => $privilege,
            'message' => 'Privilège récupéré avec succès'
        ]);
    }

    public function update(Request $request, Privilege $privilege)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048"
            ]);

            // Ne plus modifier le slug lors de la mise à jour

            if ($request->hasFile("image")) {
                if ($privilege->image) {
                    Storage::disk("public")->delete($privilege->image);
                }
                $validated['image'] = $request->file("image")->store("privileges", "public");
            }

            $privilege->update($validated);

            return response()->json([
                'success' => true,
                'data' => $privilege,
                'message' => 'Privilège mis à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du privilège'
            ], 500);
        }
    }

    public function destroy(Privilege $privilege)
    {
        try {
            if ($privilege->image) {
                Storage::disk("public")->delete($privilege->image);
            }
            $privilege->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Privilège supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("PrivilegeController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du privilège'
            ], 500);
        }
    }
}
