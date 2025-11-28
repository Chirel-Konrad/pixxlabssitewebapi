<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SocialLoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use ApiResponse;
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Inscription d'un nouvel utilisateur",
     *     description="Crée un nouveau compte utilisateur. Un email de vérification est envoyé automatiquement. Le compte reste inactif jusqu'à la vérification de l'email.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données d'inscription de l'utilisateur",
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="John Doe", description="Nom complet de l'utilisateur"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", description="Adresse email unique"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="Password123!", description="Mot de passe (minimum 8 caractères)"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="Password123!", description="Confirmation du mot de passe"),
     *             @OA\Property(property="phone", type="string", maxLength=20, nullable=true, example="+229 97 00 00 00", description="Numéro de téléphone (optionnel)"),
     *             @OA\Property(property="role", type="string", enum={"user", "admin", "superadmin"}, example="user", description="Rôle de l'utilisateur (par défaut: user)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie. Email de vérification envoyé.",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *             @OA\Property(property="message", type="string", example="Inscription réussie. Un email de vérification a été envoyé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'slug' => Str::slug($validated['name']) . '-' . uniqid(),
            'provider' => null,
            'provider_id' => null,
            'is_2fa_enable' => false,
            'email_verified_at' => null,
            'status' => 'inactive',
            'role' => $validated['role'] ?? 'user',
        ]);

        $user->sendEmailVerificationNotification();
        $token = $user->createToken('PolariixToken')->accessToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Inscription réussie. Un email de vérification a été envoyé.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/email/verify/{id}/{hash}",
     *     tags={"Authentication"},
     *     summary="Vérification de l'email utilisateur",
     *     description="Valide l'adresse email de l'utilisateur via le lien reçu par email. Active automatiquement le compte après vérification.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="Hash de vérification de l'email",
     *         @OA\Schema(type="string", example="a1b2c3d4e5f6...")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email vérifié avec succès. Statut activé."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lien de vérification invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Lien de vérification invalide.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->errorResponse('Lien de vérification invalide.', 400);
        }

        if ($user->hasVerifiedEmail() && !$user->is_2fa_enable) {
            return $this->successResponse(null, 'Email déjà vérifié.');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
        $user->update(['status' => 'active']);

        return $this->successResponse([
            'user' => new UserResource($user),
        ], 'Email vérifié avec succès. Statut activé.');
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Connexion utilisateur classique",
     *     description="Authentifie un utilisateur avec email et mot de passe. Crée une session et retourne un token Passport. Gère la vérification 2FA si activée.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie ou 2FA requise",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="boolean", example=true),
     *                     @OA\Property(property="message", type="string", example="Connexion réussie"),
     *                     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *                     @OA\Property(property="session_id", type="string", example="abc123xyz..."),
     *                     @OA\Property(property="user", ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="message", type="string", example="Connexion réussie, mais vérification 2FA requise. Consultez votre email."),
     *                     @OA\Property(property="two_factor_required", type="boolean", example=true)
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Identifiants invalides")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Compte banni",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte banni")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur non trouvé")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return $this->errorResponse('Utilisateur non trouvé', 404);
        }

        if ($user->status == 'banned') {
            return $this->errorResponse('Compte banni', 403);
        }

        if (!Auth::attempt($validated)) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        if ($user->is_2fa_enable && $user->status == 'inactive') {
            $user->sendEmailVerificationNotification();
            Auth::logout();

            return $this->successResponse([
                'two_factor_required' => true
            ], 'Connexion réussie, mais vérification 2FA requise. Consultez votre email.');
        }

        $token = $user->createToken('PolariixToken')->accessToken;

        session([
            'user_id' => $user->id,
            'notifications' => [],
        ]);

        return $this->successResponse([
            'status' => true,
            'token' => $token,
            'session_id' => session()->getId(),
            'user' => new UserResource($user),
        ], 'Connexion réussie');
    }

    /**
     * @OA\Post(
     *     path="/api/social-login",
     *     tags={"Authentication"},
     *     summary="Connexion via réseaux sociaux",
     *     description="Authentifie ou crée un utilisateur via un provider social (Google, Facebook, etc.). Le compte est automatiquement vérifié et activé.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"provider", "provider_id", "email", "name"},
     *             @OA\Property(property="provider", type="string", example="google", description="Nom du provider (google, facebook, etc.)"),
     *             @OA\Property(property="provider_id", type="string", example="123456789", description="ID unique du provider"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@gmail.com"),
     *             @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     *             @OA\Property(property="phone", type="string", maxLength=20, nullable=true, example="+229 97 00 00 00"),
     *             @OA\Property(property="role", type="string", enum={"user", "admin", "superadmin"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Connexion sociale réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion sociale réussie"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *             @OA\Property(property="session_id", type="string", example="abc123xyz..."),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function socialLogin(SocialLoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('provider', $validated['provider'])
                    ->where('provider_id', $validated['provider_id'])
                    ->first();

        if (!$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => null,
                'phone' => $validated['phone'] ?? null,
                'provider' => $validated['provider'],
                'provider_id' => $validated['provider_id'],
                'is_2fa_enable' => false,
                'email_verified_at' => Carbon::now(),
                'status' => 'active',
                'slug' => Str::slug($validated['name']) . '-' . uniqid(),
                'role' => $validated['role'] ?? 'user',
            ]);
        }

        $token = $user->createToken('PolariixToken')->accessToken;

        session([
            'user_id' => $user->id,
            'notifications' => [],
        ]);

        return $this->successResponse([
            'status' => true,
            'token' => $token,
            'session_id' => session()->getId(),
            'user' => new UserResource($user),
        ], 'Connexion sociale réussie', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/enable-2fa",
     *     tags={"Authentication"},
     *     summary="Activer l'authentification à deux facteurs (2FA)",
     *     description="Active la 2FA pour l'utilisateur connecté. Le compte passe en statut 'inactive' et un email de validation est envoyé.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="2FA activée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="2FA activée, un email de validation vous a été envoyé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function enable2FA(Request $request)
    {
        $user = $request->user();
        $user->is_2fa_enable = true;
        $user->status = 'inactive';
        $user->save();

        $user->sendEmailVerificationNotification();

        return $this->successResponse(null, '2FA activée, un email de validation vous a été envoyé.');
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Déconnexion utilisateur",
     *     description="Déconnecte l'utilisateur en révoquant son token Passport et en supprimant sa session. Si la 2FA est activée, le compte repasse en statut 'inactive'.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnecté avec succès. Session et token révoqués.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Aucun utilisateur connecté",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun utilisateur connecté")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Aucun utilisateur connecté', 401);
        }

        if ($user->is_2fa_enable) {
            $user->status = 'inactive';
            $user->save();
        }

        $request->user()->token()->revoke();

        session()->flush();
        session()->invalidate();
        session()->regenerateToken();

        return $this->successResponse([
            'status' => true,
        ], 'Déconnecté avec succès. Session et token révoqués.');
    }

    /**
     * @OA\Post(
     *     path="/api/password/email",
     *     tags={"Authentication"},
     *     summary="Demander un lien de réinitialisation du mot de passe",
     *     description="Envoie un email contenant un lien pour réinitialiser le mot de passe. Le compte passe en statut 'inactive' jusqu'à la réinitialisation. Le token expire après 10 minutes.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", description="Email de l'utilisateur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lien de réinitialisation envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Lien de réinitialisation envoyé par email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function sendPasswordResetLink(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return $this->errorResponse('Utilisateur non trouvé', 404);
        }

        $token = Str::random(64);

        $user->update([
            'password_reset_token' => $token,
            'password_reset_sent_at' => now(),
            'status' => 'inactive',
        ]);
        $user->save();

        $resetLink = url("/api/password/reset?token={$token}");

        Mail::raw("Cliquez ici pour réinitialiser votre mot de passe : $resetLink", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Réinitialisation de votre mot de passe');
        });

        return $this->successResponse(null, 'Lien de réinitialisation envoyé par email');
    }

    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     tags={"Authentication"},
     *     summary="Réinitialiser le mot de passe",
     *     description="Réinitialise le mot de passe de l'utilisateur avec le token reçu par email. Le compte est automatiquement réactivé après la réinitialisation. Le token expire après 10 minutes.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abc123def456...", description="Token de réinitialisation reçu par email"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8, example="NewPassword123!", description="Nouveau mot de passe (min 8 caractères)"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword123!", description="Confirmation du nouveau mot de passe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('password_reset_token', $validated['token'])->first();

        if (!$user) {
            return $this->errorResponse('Token invalide', 400);
        }

        if (Carbon::parse($user->password_reset_sent_at)->addMinutes(10)->isPast()) {
            return $this->errorResponse('Token expiré', 400);
        }

        $user->password = bcrypt($validated['password']);
        $user->status = 'active';
        $user->password_reset_token = null;
        $user->password_reset_sent_at = null;
        $user->save();

        return $this->successResponse(null, 'Mot de passe réinitialisé avec succès');
    }
}