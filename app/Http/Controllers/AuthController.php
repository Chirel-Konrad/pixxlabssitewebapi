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
     *     path="/api/v1/register",
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
     *             @OA\Property(property="terms", type="boolean", example=true, description="Accepter les termes et conditions (obligatoire)"),
     *             @OA\Property(property="phone", type="string", maxLength=20, nullable=true, example="+229 97 00 00 00", description="Numéro de téléphone (optionnel)"),
     *             @OA\Property(property="role", type="string", enum={"user", "admin", "superadmin"}, example="user", description="Rôle de l'utilisateur (par défaut: user)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie. Email de vérification envoyé.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inscription réussie. Un email de vérification a été envoyé."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
     public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:user,admin,superadmin',
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
            'email_verified_at' => null,
            'status' => 'inactive',
            'role' => $request->role ?? 'user',
            'terms_accepted_at' => \Carbon\Carbon::now(),
        ]);

        $user->sendEmailVerificationNotification();
        $token = $user->createToken('PolariixToken')->accessToken;

        return $this->successResponse([
            'user' => $user->makeHidden(['password']),
            'token' => $token,
        ], 'Inscription réussie. Un email de vérification a été envoyé.', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/email/verify/{id}/{hash}",
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
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email vérifié avec succès. Statut activé."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
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
            // Pour l'UX, on peut aussi retourner une vue d'erreur, mais pour l'instant une erreur JSON est acceptable si le lien est invalide
             return $this->errorResponse('Lien de vérification invalide.', 400); 
        }

        $expires = $request->query('expires');
        $signature = $request->query('signature');
        $queryParams = "?expires={$expires}&signature={$signature}";

        if ($user->hasVerifiedEmail() && !$user->is_2fa_enable) {
             return redirect("https://piixlabs-v2.vercel.app/auth/verify-email/{$id}/{$hash}{$queryParams}");
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
        $user->update(['status' => 'active']);

        return redirect("https://piixlabs-v2.vercel.app/auth/verify-email/{$id}/{$hash}{$queryParams}");
    }


    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Authentication"},
     *     summary="Connexion utilisateur classique",
     *     description="Authentifie un utilisateur avec email et mot de passe. Crée une session et retourne un token Passport. Gère la vérification 2FA si activée.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!"),
     *             @OA\Property(property="remember_me", type="boolean", example=true, description="Se souvenir de moi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie ou 2FA requise",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="message", type="string", example="Connexion réussie"),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *                         @OA\Property(property="session_id", type="string", example="abc123xyz..."),
     *                         @OA\Property(property="user", ref="#/components/schemas/User")
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=true),
     *                     @OA\Property(property="message", type="string", example="Connexion réussie, mais vérification 2FA requise. Consultez votre email."),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="two_factor_required", type="boolean", example=true)
     *                     )
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
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) return $this->errorResponse('Utilisateur non trouvé', 404);
        if ($user->status == 'banned') return $this->errorResponse('Compte banni', 403);
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return $this->errorResponse('Veuillez vérifier votre adresse email avant de vous connecter.', 403);
        }

        if ($user->is_2fa_enable && $user->status == 'inactive') {
            $user->sendEmailVerificationNotification();
            Auth::logout();
            return $this->successResponse([
                'two_factor_required' => true
            ], 'Connexion réussie, mais vérification 2FA requise. Consultez votre email.');
        }

        $tokenResult = $user->createToken('PolariixToken');
        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = \Carbon\Carbon::now()->addWeeks(4);
        }
        $token->save();
        
        $accessToken = $tokenResult->accessToken;

        session([
            'user_id' => $user->id,
            'notifications' => [],
        ]);

        return $this->successResponse([
            'token' => $token,
            'session_id' => session()->getId(),
            'user' => $user->makeHidden(['password']),
        ], 'Connexion réussie');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/social-login",
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
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion sociale réussie"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGc..."),
     *                 @OA\Property(property="session_id", type="string", example="abc123xyz..."),
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function socialLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|string',
            'provider_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:user,admin,superadmin',
        ]);

        $user = User::where('provider', $request->provider)
                    ->where('provider_id', $request->provider_id)
                    ->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(16)),
                'phone' => $request->phone,
                'provider' => $request->provider,
                'provider_id' => $request->provider_id,
                'is_2fa_enable' => false,
                'email_verified_at' => Carbon::now(),
                'status' => 'active',
                'slug' => Str::slug($request->name) . '-' . uniqid(),
                'role' => $request->role ?? 'user',
            ]);
        }

        $token = $user->createToken('PolariixToken')->accessToken;

        session([
            'user_id' => $user->id,
            'notifications' => [],
        ]);

        return $this->successResponse([
            'token' => $token,
            'session_id' => session()->getId(),
            'user' => $user->makeHidden(['password']),
        ], 'Connexion sociale réussie', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/enable-2fa",
     *     tags={"Authentication"},
     *     summary="Activer l'authentification à deux facteurs (2FA)",
     *     description="Active la 2FA pour l'utilisateur connecté. Le compte passe en statut 'inactive' et un email de validation est envoyé.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="2FA activée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="2FA activée, un email de validation vous a été envoyé."),
     *             @OA\Property(property="data", type="object", nullable=true)
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
     *     path="/api/v1/logout",
     *     tags={"Authentication"},
     *     summary="Déconnexion utilisateur",
     *     description="Déconnecte l'utilisateur en révoquant son token Passport et en supprimant sa session. Si la 2FA est activée, le compte repasse en statut 'inactive'.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnecté avec succès. Session et token révoqués."),
     *             @OA\Property(property="data", type="object", nullable=true)
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

        // Revoke Access Token safely
        $token = $user->token();
        if ($token) {
            $token->revoke();
            // $token->delete(); // Avoid hard delete to keep history if needed, or wrap in try/catch if strictly required
        }

        // Handle Session safely (API requests might not have session)
        try {
            if ($request->hasSession()) {
                $request->session()->flush();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        } catch (\Exception $e) {
            // Ignore session errors for API clients
        }

        return $this->successResponse(null, 'Déconnecté avec succès.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/password/email",
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
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lien de réinitialisation envoyé par email"),
     *             @OA\Property(property="data", type="object", nullable=true)
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
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) return $this->errorResponse('Utilisateur non trouvé', 404);

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Store hashed OTP in password_reset_tokens table
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
                'attempts' => 0,
                'verified_at' => null,
            ]
        );

        // Send OTP via email
        try {
            Mail::to($user->email)->send(new \App\Mail\PasswordResetOTP($otp, $user->name));
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de l\'envoi de l\'email.', 500);
        }

        return $this->successResponse(null, 'Un code de vérification a été envoyé à votre adresse email.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/password/reset",
     *     tags={"Authentication"},
     *     summary="Réinitialiser le mot de passe",
     *     description="Réinitialise le mot de passe de l'utilisateur avec le token reçu par email.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", description="Token de réinitialisation"),
     *             @OA\Property(property="email", type="string", format="email", description="Email de l'utilisateur"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Token invalide ou expiré")
     * )
     */
    /**
     * @OA\Post(
     *     path="/api/v1/password/verify-code",
     *     tags={"Authentication"},
     *     summary="Vérifier le code OTP",
     *     description="Vérifie le code OTP reçu par email avant la réinitialisation du mot de passe.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "code"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="code", type="string", example="123456", description="Code OTP à 6 chiffres")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code vérifié avec succès")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Code invalide ou expiré"),
     *     @OA\Response(response=429, description="Trop de tentatives")
     * )
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->first();

        if (!$record) {
            return $this->errorResponse('Aucune demande de réinitialisation trouvée pour cet email', 404);
        }

        // Check expiration (10 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse('Code expiré. Veuillez demander un nouveau code.', 400);
        }

        // Check attempts (max 3)
        if ($record->attempts >= 3) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse('Trop de tentatives. Veuillez demander un nouveau code.', 429);
        }

        // Verify OTP
        if (!Hash::check($request->code, $record->token)) {
            // Increment attempts
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->increment('attempts');
            
            $remainingAttempts = 3 - ($record->attempts + 1);
            return $this->errorResponse("Code incorrect. Il vous reste {$remainingAttempts} tentative(s).", 400);
        }

        // Mark as verified
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->update(['verified_at' => now()]);

        return $this->successResponse(null, 'Code vérifié avec succès. Vous pouvez maintenant réinitialiser votre mot de passe.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/password/reset",
     *     tags={"Authentication"},
     *     summary="Réinitialiser le mot de passe",
     *     description="Réinitialise le mot de passe de l\'utilisateur avec le code OTP vérifié.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "code", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="code", type="string", example="123456"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
         *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Code invalide ou non vérifié")
     * )
     */
     public function resetPassword(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check code validity and verification status
        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->first();

        if (!$record) {
            return $this->errorResponse('Aucune demande de réinitialisation trouvée', 404);
        }

        // Check if code was verified
        if (!$record->verified_at) {
            return $this->errorResponse('Code non vérifié. Veuillez d\'abord vérifier le code.', 400);
        }

        // Check expiration
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse('Code expiré', 400);
        }

        // Verify code one last time
        if (!Hash::check($request->code, $record->token)) {
            return $this->errorResponse('Code invalide', 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) return $this->errorResponse('Utilisateur non trouvé', 404);

        $user->password = Hash::make($request->password);
        $user->status = 'active';
        $user->save();

        // Delete the code
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->successResponse(null, 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }
}