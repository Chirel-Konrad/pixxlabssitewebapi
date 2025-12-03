<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="PolariixApi",
 *     description="Documentation complète de l'API - Tous les endpoints sont accessibles ici",
 *     @OA\Contact(
 *         email="contact@votreapi.com",
 *         name="Support API"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Serveur API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Authentification via JWT Token. Entrez votre token sans le préfixe 'Bearer'"
 * )
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     title="Réponse de succès standard",
 *     description="Structure standard pour les réponses réussies",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Opération réussie"),
 *     @OA\Property(property="data", type="object", nullable=true),
 *     @OA\Property(property="meta", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Réponse d'erreur standard",
 *     description="Structure standard pour les réponses d'erreur",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Une erreur est survenue"),
 *     @OA\Property(property="code", type="integer", example=400),
 *     @OA\Property(property="errors", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     title="Erreur de validation",
 *     description="Structure pour les erreurs de validation",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ErrorResponse"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 example={"email": {"L'email est invalide"}}
 *             )
 *         )
 *     }
 * )
 */
abstract class Controller
{
    //
}