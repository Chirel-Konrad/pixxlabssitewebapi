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
