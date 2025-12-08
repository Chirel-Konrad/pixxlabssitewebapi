<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Http\Resources\ContactResource;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/contacts",
     *     tags={"Contacts"},
     *     summary="Liste des contacts",
     *     description="Récupère tous les messages de contact",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function index()
    {
        try {
            $contacts = Contact::all();
            return $this->successResponse(ContactResource::collection($contacts), 'Liste des contacts récupérée avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération des contacts", 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/v1/contacts",
     *     tags={"Contacts"},
     *     summary="Envoyer un message de contact",
     *     description="Enregistre un nouveau message de contact",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firstname", "lastname", "email", "message"},
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="lastname", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="message", type="string", example="Bonjour...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message envoyé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(StoreContactRequest $request)
    {
        try {
            $validated = $request->validated();

            $validated['slug'] = Str::slug($validated['firstname'] . ' ' . $validated['lastname']) . '-' . uniqid();

            $contact = Contact::create($validated);

            return $this->successResponse(new ContactResource($contact), 'Message envoyé avec succès', 201);
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la création du contact", 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/contacts/{contact}",
     *     tags={"Contacts"},
     *     summary="Détails d'un contact",
     *     description="Récupère un message de contact par ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="contact",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Get(
     *     path="/api/v1/contacts/slug/{slug}",
     *     tags={"Contacts"},
     *     summary="Consulter un contact via son slug (URL publique SEO‑friendly)",
     *     description="Récupère un contact par son slug URL‑friendly. À utiliser côté front pour des URLs lisibles et pour éviter d'exposer des IDs incrémentaux (anti‑énumération).",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Slug du contact",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function show(Contact $contact)
    {
        try {
            return $this->successResponse(new ContactResource($contact), 'Contact récupéré avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la récupération du contact", 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/v1/contacts/{contact}",
     *     tags={"Contacts"},
     *     summary="Mettre à jour un message par ID",
     *     description="Met à jour un message de contact existant via son ID.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="contact",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Message modifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/v1/contacts/slug/{slug}",
     *     tags={"Contacts"},
     *     summary="Mettre à jour un contact via son slug (référence URL‑friendly)",
     *     description="Met à jour un message de contact en l'identifiant par son slug public, pratique quand seul l'URL publique est connue côté client.",
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
     *             @OA\Property(property="message", type="string", example="Message modifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message mis à jour",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        try {
            $validated = $request->validated();

            // Ne plus modifier le slug lors de la mise à jour

            $contact->update($validated);

            return $this->successResponse(new ContactResource($contact), 'Contact mis à jour avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la mise à jour du contact", 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/contacts/{contact}",
     *     tags={"Contacts"},
     *     summary="Supprimer un contact",
     *     description="Supprime un message de contact",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="contact",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact supprimé",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function destroy(Contact $contact)
    {
        try {
            $contact->delete();
            return $this->successResponse(null, "Contact supprimé avec succès");
        } catch (\Exception $e) {
            return $this->errorResponse("Erreur lors de la suppression du contact", 500, $e->getMessage());
        }
    }
}
