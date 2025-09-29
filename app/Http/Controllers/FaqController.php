<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Faq::with('answers'); // Charge les réponses

            // Filtre par type si fourni
            if ($request->has('type') && in_array($request->type, ['home','webinars','partner','AI'])) {
                $query->where('type', $request->type);
            }

            $faqs = $query->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $faqs,
                'message' => 'FAQs récupérées avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des FAQs: ' . $e->getMessage()
            ], 500);
        }
    }



public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'type' => 'required|in:home,webinars,partner,AI',
            'question' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|string|max:1000',
        ]);

        // Génération automatique du slug
        $validated['slug'] = Str::slug($validated['question']) . '-' . uniqid();

        $faq = Faq::create([
            'type' => $validated['type'],
            'question' => $validated['question'],
            'description' => $validated['description'] ?? null,
            'slug' => $validated['slug'],
        ]);

        foreach ($validated['answers'] as $answerText) {
            $faq->answers()->create(['answer' => $answerText]);
        }

        return response()->json([
            'success' => true,
            'data' => $faq->load('answers'),
            'message' => 'FAQ créée avec succès'
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création de la FAQ: ' . $e->getMessage()
        ], 500);
    }
}


   public function update(Request $request, Faq $faq)
{
    try {
        $validated = $request->validate([
            'type' => 'sometimes|in:home,webinars,partner,AI',
            'question' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:255',
            'answers' => 'sometimes|array',
            'answers.*' => 'required|string|max:1000',
        ]);

        $updateData = [
            'type' => $validated['type'] ?? $faq->type,
            'question' => $validated['question'] ?? $faq->question,
            'description' => $validated['description'] ?? $faq->description,
        ];

        // Ne plus modifier le slug lors de la mise à jour

        $faq->update($updateData);

        if (isset($validated['answers'])) {
            $faq->answers()->delete();
            foreach ($validated['answers'] as $answerText) {
                $faq->answers()->create(['answer' => $answerText]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $faq->load('answers'),
            'message' => 'FAQ mise à jour avec succès'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour de la FAQ: ' . $e->getMessage()
        ], 500);
    }
}


    public function show(Faq $faq)
    {
        return response()->json([
            'success' => true,
            'data' => $faq->load('answers'),
            'message' => 'FAQ récupérée avec succès'
        ]);
    }

    public function destroy(Faq $faq)
    {
        try {
            $faq->delete(); // supprime automatiquement les réponses grâce au cascade
            return response()->json([
                'success' => true,
                'message' => 'FAQ supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la FAQ: ' . $e->getMessage()
            ], 500);
        }
    }
}
