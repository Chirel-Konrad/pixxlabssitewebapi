<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Piixlabs API Documentation',
            ],

            'routes' => [
                // La route qui va générer et servir le document JSON/YAML
                // Le document sera accessible à [APP_URL]/api/documentation/api-docs.json
                'api' => 'api/documentation',
            ],
            
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                // Chemin vers les assets Swagger UI (corrigé dans votre script de déploiement)
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'docs/asset/'),

                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                'annotations' => [
                    base_path('app'),
                ],
            ],
            
            // ✅ CORRECTION CRITIQUE 1 : Définir l'URL du document JSON pour l'interface utilisateur.
            // Nous allons construire l'URL complète en utilisant APP_URL et le chemin de la route.
            'extra_config' => [
                'urls' => [
                    [
                        // URL CORRIGÉE : Utilise la variable L5_SWAGGER_CONST_HOST (qui est APP_URL)
                        // suivi du chemin de la route ('api/documentation') et du nom du fichier ('api-docs.json')
                        'url' => env('L5_SWAGGER_CONST_HOST') . '/api/documentation/api-docs.json',
                        'name' => 'Piixlabs API',
                    ],
                ],
            ],

            'security_defines' => [
                /*
                 * Examples of security definitions
                 */
                /*
                'passport' => [
                    'type' => 'oauth2', // Valid values are "basic", "apiKey" or "oauth2".
                    'description' => 'Laravel passport oauth2 security scheme.',
                    'in' => 'header',
                    'flow' => 'password',
                    'authorizationUrl' => env('L5_SWAGGER_CONST_HOST') . '/oauth/authorize',
                    'tokenUrl' => env('L5_SWAGGER_CONST_HOST') . '/oauth/token',
                    'scopes' => [
                        'read' => 'A scope for read applications.',
                        'write' => 'A scope for write applications.',
                    ]
                ],
                */
            ],

            'ui' => [
                'display' => [
                    'filter' => env('L5_SWAGGER_UI_FILTERS', true),
                ],

                'authorization' => [
                    'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),
                    'oauth2' => [
                        'use_pkce_with_authorization_code_grant' => false,
                    ],
                ],
            ],
            
            // ✅ CORRECTION CRITIQUE 2 : S'assurer que L5_SWAGGER_CONST_HOST utilise APP_URL en fallback.
            // Cette valeur est utilisée pour construire l'URL dans 'extra_config' ci-dessus.
            'constants' => [
                'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', env('APP_URL', 'http://localhost')),
            ],
        ],

        // L5_SWAGGER_GENERATE_ALWAYS=true dans votre .env est correct pour le déploiement
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        // Le chemin de la documentation générée doit être absolu
        'paths' => [
            'docs' => storage_path('api-docs'),
            'api_docs' => '@OA\Info(title="Piixlabs API", version="1.0.0")',
            'asset_helper' => env('L5_SWAGGER_ASSET_HELPER', 'asset'),
        ],
    ],
];