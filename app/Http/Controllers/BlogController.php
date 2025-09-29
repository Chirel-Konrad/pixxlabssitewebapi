<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
class BlogController extends Controller
{
    public function index()
    {
        try {
            // Pagination + chargement des relations si nécessaire
            $blogs = Blog::with('user', 'comments')->latest()->paginate(10);
            return response()->json(['success' => true, 'data' => $blogs]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des articles',
                'message' => $e->getMessage()
            ], 500);
        }
    }
public function store(Request $request)
{
    try {
        $categories = ['Action', 'Développement personnel', 'Technologie', 'Business', 'Santé', 'Lifestyle', 'Éducation', 'Divertissement', 'Culture', 'Voyage'];

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'category' => 'required|string|in:' . implode(',', $categories),
        ]);

        $validated['user_id'] = auth()->id();

        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->storeAs('uploads/blogs', time() . '_' . $request->file('image')->getClientOriginalName(), 'public');
        }

        $blog = Blog::create($validated);

        return response()->json(['success' => true, 'data' => $blog], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Erreur lors de la création de l’article',
            'message' => $e->getMessage()
        ], 500);
    }
}


    public function show(Blog $blog)
    {
        try {
            return response()->json(['success' => true, 'data' => $blog->load('user', 'comments')]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de l’article',
                'message' => $e->getMessage()
            ], 500);
        }
    }

   public function update(Request $request, Blog $blog)
{
    try {
        $categories = ['Action', 'Développement personnel', 'Technologie', 'Business', 'Santé', 'Lifestyle', 'Éducation', 'Divertissement', 'Culture', 'Voyage'];

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'category' => 'sometimes|string|in:' . implode(',', $categories),
        ]);

         // Ne plus modifier le slug lors de la mise à jour

        if ($request->hasFile('image')) {
            if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                Storage::disk('public')->delete($blog->image);
            }
            $validated['image'] = $request->file('image')
                ->storeAs('uploads/blogs', time() . '_' . $request->file('image')->getClientOriginalName(), 'public');
        }

        $blog->update($validated);

        return response()->json(['success' => true, 'data' => $blog]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Erreur lors de la mise à jour de l’article',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function destroy(Blog $blog)
    {
        try {
            if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                Storage::disk('public')->delete($blog->image);
            }
            $blog->delete();
            return response()->json(['success' => true, 'message' => 'Article supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de l’article',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
