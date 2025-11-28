<?php

namespace App\OpenApi\Schemas;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="Modèle utilisateur complet",
 *     required={"id", "name", "email", "status", "role"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="slug", type="string", example="john-doe-abc123"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+229 97 00 00 00"),
 *     @OA\Property(property="role", type="string", enum={"user", "admin", "superadmin"}, example="user"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "banned"}, example="active", description="Statut du compte"),
 *     @OA\Property(property="provider", type="string", nullable=true, example="google", description="Provider social (google, facebook, etc.)"),
 *     @OA\Property(property="provider_id", type="string", nullable=true, example="123456789"),
 *     @OA\Property(property="is_2fa_enable", type="boolean", example=false, description="Authentification à deux facteurs activée"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:30:00.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     title="Paginated Response",
 *     description="Réponse paginée standard",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(property="first", type="string", example="http://api.com/users?page=1"),
 *         @OA\Property(property="last", type="string", example="http://api.com/users?page=10"),
 *         @OA\Property(property="prev", type="string", nullable=true),
 *         @OA\Property(property="next", type="string", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=10),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=150)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="AuthToken",
 *     title="Auth Token",
 *     description="Token d'authentification",
 *     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *     @OA\Property(property="type", type="string", example="bearer"),
 *     @OA\Property(property="expires_in", type="integer", example=3600)
 * )
 */
class CommonSchemas
{
    // Ce fichier contient uniquement les annotations de schémas
}