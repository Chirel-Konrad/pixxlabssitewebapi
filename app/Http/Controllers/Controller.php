<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Mon Projet Laravel",
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
 *     url=L5_SWAGGER_CONST_HOST,
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
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints d'authentification (login, register, logout)"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="Gestion des utilisateurs"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Gestion des produits"
 * )
 */
abstract class Controller
{
    //
}