<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/faqs",
     *     tags={"FAQs"},
     *     summary="Liste des FAQs",
     *     description="Récupère la liste paginée des questions fréquentes",
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type (home, webinars, partner, AI)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"home", "webinars", "partner", "AI"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Faq")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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



/**
     * @OA\Post(
     *     path="/api/faqs",
     *     tags={"FAQs"},
     *     summary="Créer une FAQ",
     *     description="Crée une nouvelle question fréquente avec ses réponses",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "question", "answers"},
     *             @OA\Property(property="type", type="string", enum={"home", "webinars", "partner", "AI"}),
     *             @OA\Property(property="question", type="string", example="Comment ça marche ?"),
     *             @OA\Property(property="description", type="string", example="Explication"),
     *             @OA\Property(property="answers", type="array", @OA\Items(type="string", example="Voici la réponse..."))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="FAQ créée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Faq"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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


   /**
     * @OA\Put(
     *     path="/api/faqs/{faq}",
     *     tags={"FAQs"},
     *     summary="Mettre à jour une FAQ par ID",
     *     description="Met à jour une question fréquente via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="faq",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"home", "webinars", "partner", "AI"}),
     *             @OA\Property(property="question", type="string", example="Question modifiée ?"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="answers", type="array", @OA\Items(type="string", example="Réponse modifiée..."))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Faq"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/faqs/slug/{slug}",
     *     tags={"FAQs"},
     *     summary="Mettre à jour une FAQ par Slug",
     *     description="Met à jour une question fréquente via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", enum={"home", "webinars", "partner", "AI"}),
     *             @OA\Property(property="question", type="string", example="Question modifiée ?"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="answers", type="array", @OA\Items(type="string", example="Réponse modifiée..."))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ mise à jour",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Faq"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/api/faqs/{faq}",
     *     tags={"FAQs"},
     *     summary="Détails d'une FAQ par ID",
     *     description="Récupère une question fréquente via son ID.",
     *     @OA\Parameter(
     *         name="faq",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Faq"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/faqs/slug/{slug}",
     *     tags={"FAQs"},
     *     summary="Détails d'une FAQ par Slug",
     *     description="Récupère une question fréquente via son slug. Recommandé pour l'affichage public.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Faq"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Faq $faq)
    {
        return response()->json([
            'success' => true,
            'data' => $faq->load('answers'),
            'message' => 'FAQ récupérée avec succès'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/faqs/{faq}",
     *     tags={"FAQs"},
     *     summary="Supprimer une FAQ par ID",
     *     description="Supprime une FAQ via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="faq",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ supprimée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/faqs/slug/{slug}",
     *     tags={"FAQs"},
     *     summary="Supprimer une FAQ par Slug",
     *     description="Supprime une FAQ via son slug.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ supprimée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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
