# Déploiement sur cPanel via GitHub

## 1. Préparer le dépôt GitHub

Avant d’envoyer le projet sur GitHub :

- garder `.env` hors du dépôt
- garder `vendor/` hors du dépôt
- garder `node_modules/` hors du dépôt
- inclure `public/build/` dans le dépôt si le serveur cPanel ne peut pas lancer `npm run build`

Le projet contient maintenant :

- [.cpanel.yml](.cpanel.yml) pour les tâches de déploiement automatiques
- [.env.cpanel.example](.env.cpanel.example) comme base de configuration production

## 2. Vérifier la stratégie la plus propre

### Option recommandée
Faire pointer le domaine ou sous-domaine vers le dossier `public` du projet Laravel.

Exemple recommandé :

- dépôt cloné dans `/home/CPANEL_USER/repositories/Sondage`
- document root du domaine vers `/home/CPANEL_USER/repositories/Sondage/public`

Cette approche évite de modifier `index.php`.

## 3. Pousser le code vers GitHub

À faire localement avant le push :

- vérifier que `public/build/` est bien présent après le build
- commit des fichiers du projet
- push vers GitHub

## 4. Créer la base MySQL sur cPanel

Dans cPanel :

- créer une base MySQL
- créer un utilisateur MySQL
- associer l’utilisateur à la base avec tous les privilèges

Noter :

- nom de la base
- utilisateur
- mot de passe
- hostname MySQL

## 5. Importer le dépôt GitHub dans cPanel

Dans `Git Version Control` :

- choisir `Create`
- coller l’URL GitHub du dépôt
- choisir la branche à déployer
- cloner le dépôt

## 6. Configurer le fichier `.env` sur le serveur

Dans le dossier cloné sur cPanel :

- copier `.env.cpanel.example` vers `.env`
- renseigner les vraies valeurs

Points importants :

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://votre-domaine.com`
- config MySQL cPanel
- config mail si nécessaire

Ensuite générer la clé si elle n’existe pas encore :

- `php artisan key:generate`

## 7. Déployer depuis cPanel

Le fichier [.cpanel.yml](.cpanel.yml) lancera automatiquement :

- `composer install --no-dev --optimize-autoloader --no-interaction`
- `php artisan migrate --force`
- `php artisan optimize:clear`
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `php artisan storage:link`

## 8. Configurations Laravel à vérifier après déploiement

Assurer que ces dossiers sont inscriptibles :

- `storage/`
- `bootstrap/cache/`

Si cPanel ne donne pas les bons droits, corriger les permissions depuis le gestionnaire de fichiers.

## 9. Si Node.js n’est pas disponible sur cPanel

Le plus simple est :

- lancer localement `npm run build`
- pousser le dossier `public/build/` sur GitHub
- laisser cPanel déployer ces assets déjà compilés

## 10. Vérifications finales

Après le déploiement, tester :

- page d’accueil
- inscription
- connexion
- création d’un sondage
- dashboard
- partage public du sondage
- vote public
- export PDF
- export CSV

## 11. Commandes utiles en cas de problème

À lancer dans le terminal cPanel si disponible :

- `php artisan optimize:clear`
- `php artisan migrate --force`
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

## 12. Point d’attention spécifique à ce projet

Le projet utilise :

- Laravel 13
- PHP 8.3
- MySQL conseillé en production
- assets Vite dans `public/build`
- exports PDF via Dompdf

Donc sur cPanel, il faut idéalement :

- PHP 8.3 activé
- extensions PHP classiques Laravel activées
- accès Composer disponible

