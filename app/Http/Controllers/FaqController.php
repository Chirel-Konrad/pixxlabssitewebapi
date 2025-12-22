<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Http\Resources\FaqResource;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/faqs",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
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

            return $this->paginatedResponse(FaqResource::collection($faqs), 'FAQs récupérées avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération des FAQs', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/faqs",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreFaqRequest $request)
    {
        try {
            $validated = $request->validated();

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

            return $this->successResponse(new FaqResource($faq->load('answers')), 'FAQ créée avec succès', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la création de la FAQ', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/faqs/{faq}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/admin/faqs/slug/{slug}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        try {
            $validated = $request->validated();

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

            return $this->successResponse(new FaqResource($faq->load('answers')), 'FAQ mise à jour avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la mise à jour de la FAQ', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/faqs/{faq}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/faqs/slug/{slug}",
     *     tags={"FAQs"},
     *     summary="Détails d'une FAQ par Slug",
     *     description="Récupère une question fréquente via son slug. Cette route est recommandée pour les URL publiques (SEO friendly) et la sécurité, préférée à l'ID.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FAQ trouvée",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(Faq $faq)
    {
        return $this->successResponse(new FaqResource($faq->load('answers')), 'FAQ récupérée avec succès');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/faqs/{faq}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Delete(
     *     path="/api/v1/admin/faqs/slug/{slug}",
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
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function destroy(Faq $faq)
    {
        try {
            $faq->delete(); // supprime automatiquement les réponses grâce au cascade
            return $this->successResponse(null, 'FAQ supprimée avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression de la FAQ', 500, $e->getMessage());
        }
    }
}
