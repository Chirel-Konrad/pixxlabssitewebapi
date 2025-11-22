<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/contacts",
     *     tags={"Contacts"},
     *     summary="Liste des contacts",
     *     description="Récupère tous les messages de contact",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Contact"))
     *     )
     * )
     */
    public function index()
    {
        try {
            $contacts = Contact::all();
            return response()->json($contacts);
        } catch (\Exception $e) {
            return response()->json(["message" => "Erreur lors de la récupération des contacts: " . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/contacts",
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
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     )
     * )
     */
   public function store(Request $request)
{
    try {
        $validated = $request->validate([
            "firstname" => "required|string|max:255",
            "lastname" => "required|string|max:255",
            "email" => "required|email|max:255",
            "message" => "required|string",
        ]);

        $validated['slug'] = Str::slug($validated['firstname'] . ' ' . $validated['lastname']) . '-' . uniqid();


        $contact = Contact::create($validated);

        return response()->json($contact, 201);
    } catch (\Exception $e) {
        return response()->json(["message" => "Erreur lors de la création du contact: " . $e->getMessage()], 500);
    }
}
    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/contacts/{contact}",
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
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     )
     * )
     */
    public function show(Contact $contact)
    {
        try {
            return response()->json($contact);
        } catch (\Exception $e) {
            return response()->json(["message" => "Erreur lors de la récupération du contact: " . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/contacts/{contact}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Contact"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * @OA\Put(
     *     path="/api/contacts/slug/{slug}",
     *     tags={"Contacts"},
     *     summary="Mettre à jour un message par Slug",
     *     description="Met à jour un message de contact existant via son slug.",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Contact"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
   public function update(Request $request, Contact $contact)
{
    try {
        $validated = $request->validate([
            "firstname" => "required|string|max:255",
            "lastname" => "required|string|max:255",
            "email" => "required|email|max:255",
            "message" => "required|string",
        ]);

        // Ne plus modifier le slug lors de la mise à jour

        $contact->update($validated);

        return response()->json($contact);
    } catch (\Exception $e) {
        return response()->json(["message" => "Erreur lors de la mise à jour du contact: " . $e->getMessage()], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/contacts/{contact}",
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
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Contact supprimé avec succès")
     *         )
     *     )
     * )
     */
    public function destroy(Contact $contact)
{
    try {
        $contact->delete();
        return response()->json(["message" => "Contact supprimé avec succès"]);
    } catch (\Exception $e) {
        return response()->json(["message" => "Erreur lors de la suppression du contact: " . $e->getMessage()], 500);
    }
}

}
