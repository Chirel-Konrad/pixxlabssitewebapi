<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebinarController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $webinars = Webinar::latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $webinars,
                'message' => 'Webinaires récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("WebinarController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la récupération des webinaires'
            ], 500);
        }
    }

   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "whose" => "required|string|max:255",
                "date" => "required|string|max:255",
                "time" => "required|string|max:255",
                "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
                "video" => "nullable|file|mimes:mp4,mov,avi,webm|max:51200", // max 50MB
            ]);

            // Génération automatique du slug unique
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

            // Upload de l'image si présente
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')
                    ->storeAs('uploads/webinars/images', time().'_'.$request->file('image')->getClientOriginalName(), 'public');
            }

            // Upload de la vidéo si présente
            if ($request->hasFile('video')) {
                $validated['video_url'] = $request->file('video')
                    ->storeAs('uploads/webinars/videos', time().'_'.$request->file('video')->getClientOriginalName(), 'public');
            }

            $webinar = Webinar::create($validated);

            return response()->json([
                'success' => true,
                'data' => $webinar,
                'message' => 'Webinaire créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            Log::error("WebinarController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la création du webinaire'
            ], 500);
        }
    }

    public function show(Webinar $webinar)
    {
        return response()->json([
            'success' => true,
            'data' => $webinar,
            'message' => 'Webinaire récupéré avec succès'
        ]);
    }

    public function update(Request $request, Webinar $webinar)
    {
        try {
            $validated = $request->validate([
                "title" => "required|string|max:255",
                "description" => "nullable|string",
                "whose" => "required|string|max:255",
                "date" => "required|string|max:255",
                "time" => "required|string|max:255",
                "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
                "video" => "nullable|file|mimes:mp4,mov,avi,webm|max:51200", // max 50MB
            ]);

            // Ne plus modifier le slug lors de la mise à jour

            // Upload image
            if ($request->hasFile('image')) {
                if ($webinar->image && Storage::disk('public')->exists($webinar->image)) {
                    Storage::disk('public')->delete($webinar->image);
                }
                $validated['image'] = $request->file('image')
                    ->storeAs('uploads/webinars/images', time().'_'.$request->file('image')->getClientOriginalName(), 'public');
            }

            // Upload vidéo
            if ($request->hasFile('video')) {
                if ($webinar->video_url && Storage::disk('public')->exists($webinar->video_url)) {
                    Storage::disk('public')->delete($webinar->video_url);
                }
                $validated['video_url'] = $request->file('video')
                    ->storeAs('uploads/webinars/videos', time().'_'.$request->file('video')->getClientOriginalName(), 'public');
            }

            $webinar->update($validated);

            return response()->json([
                'success' => true,
                'data' => $webinar,
                'message' => 'Webinaire mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error("WebinarController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la mise à jour du webinaire'
            ], 500);
        }
    }

    public function destroy(Webinar $webinar)
    {
        try {
            $webinar->delete();
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Webinaire supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error("WebinarController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Erreur lors de la suppression du webinaire'
            ], 500);
        }
    }
}
