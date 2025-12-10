# Polariix Site Web API (Laravel 12)

Ce dépôt contient une API REST Laravel modulaire, documentée via Swagger (l5-swagger), orientée contenus (blogs, offres, produits, webinaires, etc.) avec prise en charge des slugs SEO-friendly et une sérialisation propre via Resources.

## Sommaire
- **Stack**
- **Structure du projet**
- **Schéma DB: users**
- **Ressources et routes API**
- **Authentification (état actuel)**
- **Sérialisation (Resources)**
- **Installation et exécution**
- **Variables d'environnement**
- **Documentation Swagger**
- **Seeders et données de test**

## Stack
- PHP 8.2+, Laravel 12
- l5-swagger (OpenAPI) pour la documentation
- Passport installé et configuré côté guard (`config/auth.php`) mais non routé
- Base de données: Eloquent ORM (migrations incluses)

## Structure du projet
Répertoires/fichiers clés pour comprendre l’architecture:

```
app/
  Http/
    Controllers/
      AuthController.php                 # Contrôleur d’auth (non routé actuellement)
      BlogController.php                 # CRUD + GET par slug
      BlogCommentController.php
      ContactController.php              # CRUD + GET par slug
      EvaFeatureController.php           # CRUD + GET par slug
      FaqController.php                  # CRUD + GET par slug
      NewsletterController.php           # CRUD + GET par slug
      OfferController.php                # CRUD + GET par slug
      PilierController.php               # CRUD + GET par slug
      PrivilegeController.php            # CRUD + GET par slug
      ProductController.php              # CRUD + GET par slug
      TestimonialController.php          # CRUD + GET par slug
      UserController.php                 # CRUD basique par id
      WebinarController.php              # CRUD + GET par slug
      WebinarRegistrationController.php  # Inscriptions webinaire
    Requests/                            # FormRequests (validation)
    Resources/                           # API Resources (sérialisation JSON)
    Middleware/                          # (vide actuellement)
  Models/
    User.php                             # Modèle utilisateur avec HasApiTokens
bootstrap/app.php                        # Configuration runtime (Laravel 12)
config/
  auth.php                               # guard api=passport (provider users)
  passport.php                           # Config Passport
routes/api.php                           # Définition des routes API v1
public/
  storage/                               # Accès aux fichiers uploadés via Storage::url
```

## Schéma DB: users
Défini dans `database/migrations/0001_01_01_000000_create_users_table.php` (PostgreSQL friendly, ENUMs créés via SQL):

- id (bigint)
- name (string)
- email (string, unique)
- password (string)
- phone (string, nullable)
- is_2fa_enable (boolean, default false)
- provider (string, nullable)
- provider_id (string, nullable)
- remember_token (string)
- email_verified_at (timestamp, nullable)
- image (string, nullable)
- status (enum: active | inactive | banned, default active)
- role (enum: user | admin | superadmin, default user)
- timestamps

Table de reset:
- password_reset_tokens(email PK, token, created_at)

Modèle `App\Models\User`:
- Traits: `HasApiTokens`, `Notifiable`, `HasFactory`
- Fillable: `name, email, password, phone, is_2fa_enable, provider, provider_id, status, email_verified_at, remember_token, slug, image, role`
- Casts: `email_verified_at` datetime, `password` hashed

## Ressources et routes API
Préfixe commun: `/api/v1`

Chaque ressource expose:
- Listing paginé: `GET /{resource}`
- Lecture par ID: `GET /{resource}/{id}`
- Lecture par slug: `GET /{resource}/slug/{model:slug}` (SEO-friendly, évite l’exposition d’IDs)
- Écriture: `POST /{resource}`
- Mise à jour: `PUT /{resource}/{id}` et `PUT /{resource}/slug/{model:slug}`
- Suppression: `DELETE /{resource}/{id}` et `DELETE /{resource}/slug/{model:slug}`

Ressources implémentées:
- Blogs, Blog Comments
- Contacts
- FAQs
- Newsletters
- Products
- Testimonials
- Webinars, Webinar Registrations
- Piliers
- Privileges
- Eva Features
- Offers
- Users (CRUD par id)

Note: à ce stade, les routes d’écriture sont publiques (pas de middleware auth appliqué).

## Authentification (état actuel)
- `config/auth.php`: guard `api` => driver `passport`, provider `users`.
- `App\Http\Controllers\AuthController` implémente: register, login, socialLogin, verifyEmail, enable2FA, logout, reset.
- Les routes d’auth ne sont pas exposées dans `routes/api.php`.
- Aucun middleware custom `auth.api` enregistré dans `bootstrap/app.php`.

Pour activer une auth complète (comme « Polariix »):
- Exposer les routes `/api/v1/auth/*`, `/api/v1/password/*`, `/api/v1/email/verify/*`.
- Enregistrer un alias middleware `auth.api` et protéger POST/PUT/DELETE.
- Installer Socialite si login providers requis.

## Sérialisation (Resources)
- Toutes les réponses passent par `App\Http\Resources\*Resource`.
- Gestion des images:
  - URL absolue (http/https): renvoyée telle quelle
  - Chemin local: `url(Storage::url(...))`

## Installation et exécution
1) Dépendances
- Composer
  - `composer install`
- (optionnel) Node
  - `npm install`

2) ENV et clés
- Copier `.env.example` en `.env`, ajuster DB/Mail/Storage.
- `php artisan key:generate`

3) Base de données
- `php artisan migrate`
- (optionnel) `php artisan db:seed`

4) Lancer
- Serveur: `php artisan serve`
- (optionnel) Vite: `npm run dev`

## Variables d'environnement
- DB_* (connexion base)
- MAIL_* (email vérif/reset si utilisé par AuthController)
- STORAGE_DRIVER (public par défaut)
- (si Passport pleinement activé) PASSPORT_PRIVATE_KEY / PASSPORT_PUBLIC_KEY

## Documentation Swagger
- Générer: `php artisan l5-swagger:generate`
- UI: `/api/documentation`

## Seeders et données de test
- `database/seeders/*` (produits, webinaires, etc.).
- Exemple: `php artisan db:seed --class=ProductSeeder`.

---

### Note sur la sécurité
Actuellement, aucune route n’est protégée. Pour appliquer une politique de sécurité:
- Créer un middleware `auth.api` (Passport) et l’aliaser dans `bootstrap/app.php`.
- Regrouper toutes les routes d’écriture (POST/PUT/DELETE) dans un groupe `->middleware('auth.api')`.
- Exposer les routes d’auth (register/login/logout, verify email, reset password). 

Cette documentation reflète l’état présent du dépôt et met en évidence les points d’intégration nécessaires pour activer l’authentification complète si souhaité.
