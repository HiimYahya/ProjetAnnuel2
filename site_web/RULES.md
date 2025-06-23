# Règles Cursor pour le Projet Site Web

## Structure du Projet

### Organisation des dossiers
- `@site_web` : Projet principal
- `@site_web/fonctions` : Fonctions PHP réutilisables
- `@site_web/features` : Fonctionnalités principales
- `@site_web/assets` : Ressources statiques

### Fichiers importants
- `fonctions/header.php` : En-tête du site
- `fonctions/header_connected.php` : En-tête pour utilisateurs connectés
- `fonctions/footer.php` : Pied de page
- `fonctions/darkmode.php` : Gestion du mode sombre
- `fonctions/db.php` : Connexion à la base de données
- `features/backend.php` : Logique backend principale

## Règles de formulation des demandes

### Syntaxe de base
```markdown
@site_web/[dossier]/[fichier]
Description de la demande
```

### Exemples de bonnes formulations
1. Modification de fonctionnalité existante :
   ```
   @site_web/fonctions/header.php
   Je souhaite modifier la fonction X pour ajouter Y
   ```

2. Création de nouvelle fonctionnalité :
   ```
   @site_web/features
   Je veux créer une nouvelle fonctionnalité Z dans le dossier public
   ```

3. Modification de style :
   ```
   @site_web/fonctions/darkmode.php
   Je souhaite modifier le comportement du mode sombre pour X
   ```

## Bonnes pratiques de développement

### Sécurité
- Toujours valider les entrées utilisateur
- Utiliser des requêtes préparées pour la base de données
- Ne jamais stocker de données sensibles en clair
- Vérifier les permissions avant chaque action

### Base de données
- Préfixer les tables avec `pa_`
- Utiliser des clés étrangères pour les relations
- Documenter les modifications de schéma
- Sauvegarder avant toute modification majeure

### Interface utilisateur
- Respecter le système de dark mode
- Maintenir la cohérence visuelle
- Adapter l'interface pour mobile et desktop
- Utiliser les icônes définies dans `icons.php`

### Fonctionnalités principales
- Paiements : `features/paiements.php`
- Prestataires : `features/prestataires.php`
- Livraisons : `features/livraisons.php`
- Clients : `features/clients.php`
- Livreurs : `features/livreurs.php`

## Tests et validation

### Avant chaque modification
1. Vérifier la compatibilité avec les fonctionnalités existantes
2. Tester en mode connecté et non connecté
3. Vérifier le responsive design
4. Tester le dark mode

### Après chaque modification
1. Vérifier les logs d'erreurs
2. Tester les fonctionnalités liées
3. Vérifier la performance
4. Documenter les changements

## Documentation

### Commentaires de code
- Utiliser le format PHPDoc pour les fonctions
- Documenter les paramètres et valeurs de retour
- Expliquer les choix techniques complexes
- Maintenir la documentation à jour

### Structure des commentaires
```php
/**
 * Description de la fonction
 * 
 * @param type $param Description du paramètre
 * @return type Description de la valeur de retour
 * @throws Exception Description de l'exception
 */
```

## Gestion des erreurs

### Logging
- Utiliser les niveaux appropriés (ERROR, WARNING, INFO)
- Inclure le contexte nécessaire
- Ne pas logger d'informations sensibles
- Maintenir les logs propres et lisibles

### Messages d'erreur
- Messages clairs et informatifs
- Pas de détails techniques pour les utilisateurs
- Logs détaillés pour les développeurs
- Gestion appropriée des exceptions

## Performance

### Optimisations
- Minimiser les requêtes à la base de données
- Utiliser le cache quand approprié
- Optimiser les images et ressources
- Minimiser les requêtes HTTP

### Bonnes pratiques
- Utiliser la mise en cache du navigateur
- Compresser les ressources
- Optimiser les requêtes SQL
- Utiliser le lazy loading pour les images

## Maintenance

### Nettoyage régulier
- Supprimer le code inutilisé
- Mettre à jour les dépendances
- Optimiser la base de données
- Nettoyer les logs anciens

### Sauvegardes
- Sauvegarder la base de données quotidiennement
- Conserver les versions stables
- Documenter les procédures de restauration
- Tester régulièrement les sauvegardes 