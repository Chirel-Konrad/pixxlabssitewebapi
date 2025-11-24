# Guide de Déploiement sur Render avec Neon PostgreSQL

## Problème Identifié

L'erreur `SASL authentication failed` indique que les identifiants de connexion à la base de données Neon ne sont pas correctement configurés sur Render.

## Solution: Configuration des Variables d'Environnement sur Render

### 1. Accéder aux Variables d'Environnement

1. Connectez-vous à votre tableau de bord Render
2. Sélectionnez votre service web
3. Allez dans l'onglet **Environment**

### 2. Configurer les Variables de Base de Données

Ajoutez les variables d'environnement suivantes avec les valeurs de votre base de données Neon:

```env
DB_CONNECTION=pgsql
DB_HOST=ep-frosty-fog-agy2b3cu-pooler.c-2.eu-central-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=votre_nom_de_base
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
DB_SSLMODE=require
```

> **IMPORTANT**: Utilisez les identifiants exacts fournis par Neon. Vous pouvez les trouver dans:
> - Tableau de bord Neon → Votre projet → Connection Details
> - La chaîne de connexion complète ressemble à: `postgresql://user:password@host/database?sslmode=require`

### 3. Autres Variables d'Environnement Requises

```env
APP_NAME="Laravel API"
APP_ENV=production
APP_KEY=base64:VotreClé
APP_DEBUG=false
APP_URL=https://piixlabs-site-web-api.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=error

SESSION_DRIVER=file
SESSION_LIFETIME=120

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

# Swagger
L5_SWAGGER_CONST_HOST=https://piixlabs-site-web-api.onrender.com
```

### 4. Vérification de la Configuration Neon

Notre configuration dans `config/database.php` détecte automatiquement les hôtes Neon et ajoute l'option `endpoint`:

```php
'database' => (function() {
    $dbname = env('DB_DATABASE', 'laravel');
    $host = env('DB_HOST', '');
    
    // Si utilisant Neon Tech, ajoute l'option endpoint
    if (str_contains($host, 'neon.tech')) {
        $endpointId = explode('.', $host)[0];
        return "dbname={$dbname} options=endpoint={$endpointId}";
    }
    
    return $dbname;
})(),
```

### 5. Redéploiement

Après avoir configuré les variables d'environnement:

1. Cliquez sur **Manual Deploy** → **Clear build cache & deploy**
2. Ou poussez un nouveau commit pour déclencher un redéploiement automatique

### 6. Vérification des Logs

Surveillez les logs de déploiement pour confirmer que:
- Les migrations s'exécutent correctement
- Aucune erreur d'authentification n'apparaît
- Le service démarre avec succès

## Dépannage

### Si l'erreur persiste:

1. **Vérifiez les identifiants Neon**:
   - Connectez-vous à votre tableau de bord Neon
   - Vérifiez que la base de données est active
   - Copiez les identifiants exacts depuis "Connection Details"

2. **Testez la connexion localement**:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

3. **Vérifiez le mode SSL**:
   - Neon requiert `sslmode=require`
   - Assurez-vous que `DB_SSLMODE=require` est défini

4. **Vérifiez l'IP allowlist** (si configurée):
   - Neon permet de restreindre les connexions par IP
   - Render utilise des IPs dynamiques, donc cette option doit être désactivée ou configurée pour autoriser toutes les IPs

## Commandes Utiles sur Render

Pour exécuter des commandes artisan sur Render, utilisez le Shell:

1. Allez dans votre service → **Shell**
2. Exécutez:
   ```bash
   php artisan migrate:status
   php artisan config:cache
   php artisan route:cache
   ```

## Notes Importantes

- **APP_KEY**: Doit être généré avec `php artisan key:generate` et ajouté aux variables d'environnement
- **APP_DEBUG**: Toujours `false` en production
- **DB_PASSWORD**: Assurez-vous qu'il n'y a pas d'espaces ou de caractères spéciaux non échappés
- **Neon Pooler**: L'URL contient `-pooler` ce qui est correct pour les connexions depuis des services comme Render
