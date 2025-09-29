<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use App\Helpers\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;



class AuthController extends Controller
{
    // Inscription utilisateur classique
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:user,admin,superadmin', // validation du rôle
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'provider' => null,
            'provider_id' => null,
            'is_2fa_enable' => false,
            'email_verified_at' => null,  // Pas encore vérifié
            'status' => 'inactive',       // Statut inactif avant vérif
            'role' => $request->role ?? 'user', // rôle par défaut "user"
        ]);


        // Envoi du mail de vérification directement après inscription
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('PolariixToken')->accessToken;

        return response()->json([
            'user' => $user->makeHidden(['password']),
            'token' => $token,
            'message' => 'Inscription réussie. Un email de vérification a été envoyé.',
        ],201, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Vérification du hash de l'email
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Lien de vérification invalide.'], 400);
        }

        // Vérifie si l'email est déjà confirmé
        if ($user->hasVerifiedEmail() && !$user->is_2fa_enable) {
            return response()->json(['message' => 'Email déjà vérifié.'], 200);
        }

        // Marque l'email comme vérifié
        $user->markEmailAsVerified();
        event(new Verified($user));

        // Met à jour le statut de l'utilisateur
        $user->update(['status' => 'active']);

        return response()->json([
            'message' => 'Email vérifié avec succès. Statut activé.',
            'user' => $user->makeHidden(['password']),
        ],200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }


    // Connexion utilisateur classique
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
//dd($user);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        if ($user->status == 'banned') {
            return response()->json(['message' => 'Compte banni'], 403);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        // Vérification 2FA
        if ($user->is_2fa_enable && $user->status == 'inactive') {
            $user->sendEmailVerificationNotification();
            Auth::logout();

            return response()->json([
                'message' => 'Connexion réussie, mais vérification 2FA requise. Consultez votre email.',
                'two_factor_required' => true
            ]);
        }

        // Création du token Passport
        $token = $user->createToken('PolariixToken')->accessToken;

        // 🔑 Création / ouverture de la session Laravel
        session([
            'user_id' => $user->id,
            'notifications' => [], // tu pourras y pousser les notifications non lues
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'session_id' => session()->getId(), // Utile côté front si tu veux exploiter la session
            'user' => $user->makeHidden(['password']),
        ], 200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // Auth via provider (Google, Facebook etc.) - Optionnel
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'provider_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:user,admin,superadmin', // validation du rôle
        ]);

        $user = User::where('provider', $request->provider)
                    ->where('provider_id', $request->provider_id)
                    ->first();

        if (!$user) {
            // Création d’un nouvel utilisateur social
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => null,
                'phone' => $request->phone,
                'provider' => $request->provider,
                'provider_id' => $request->provider_id,
                'is_2fa_enable' => false,
                'email_verified_at' => Carbon::now(),
                'status' => 'active',
                'slug' => Str::slug($request->name) . '-' . uniqid(),
                'role' => $request->role ?? 'user', // rôle par défaut "user"
            ]);
        }

        // Création du token Passport
        $token = $user->createToken('PolariixToken')->accessToken;

        // 🔑 Création / ouverture de la session Laravel
        session([
            'user_id' => $user->id,
            'notifications' => [],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Connexion sociale réussie',
            'token' => $token,
            'session_id' => session()->getId(),
            'user' => $user->makeHidden(['password']),
        ], 201, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }


    //activer/désactiver la 2FA
    public function enable2FA(Request $request)
    {
        $user = $request->user();
        $user->is_2fa_enable = true;
        $user->status = 'inactive'; // bloquer accès jusqu'à validation
        $user->save();

        // Envoi du mail de vérification 2FA (via email verification)
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => '2FA activée, un email de validation vous a été envoyé.'],200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // Déconnexion utilisateur (révocation du token actuel + suppression de la session)
    public function logout(Request $request)
    {
        $user = $request->user(); // Récupère l'utilisateur connecté via le token

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun utilisateur connecté'
            ], 401);
        }

        // Si 2FA activé, on repasse en "inactive"
        if ($user->is_2fa_enable) {
            $user->status = 'inactive';
            $user->save();
        }

        // 🔑 Révoquer le token Passport
        $request->user()->token()->revoke();

        // 🔑 Supprimer la session Laravel associée
        session()->flush(); // Vide complètement la session
        session()->invalidate(); // Invalide l’ID actuel
        session()->regenerateToken(); // Regénère le CSRF token (sécurité)

        return response()->json([
            'status' => true,
            'message' => 'Déconnecté avec succès. Session et token révoqués.'
        ], 200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Générer token unique
        $token = Str::random(64);

        // Mettre à jour utilisateur
        $user->update([
            'password_reset_token' => $token,
            'password_reset_sent_at' => now(),
            'status' => 'inactive',
        ]);
                $user->save();

        // Envoyer mail avec lien (exemple très simple)
        $resetLink = url("/api/password/reset?token={$token}");

        // Envoie du mail (tu peux utiliser Notification ou Mail)
        Mail::raw("Cliquez ici pour réinitialiser votre mot de passe : $resetLink", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Réinitialisation de votre mot de passe');
        });

        return response()->json(['message' => 'Lien de réinitialisation envoyé par email'],200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('password_reset_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token invalide'], 400);
        }

        // Optionnel: vérifier expiration (ex: 60 minutes)
        if (Carbon::parse($user->password_reset_sent_at)->addMinutes(10)->isPast()) {
            return response()->json(['message' => 'Token expiré'], 400);
        }

        // Mettre à jour le mot de passe et réactiver le compte
        $user->password = bcrypt($request->password);
        $user->status = 'active';
        $user->password_reset_token = null;
        $user->password_reset_sent_at = null;
        $user->save();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès'],200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

}
