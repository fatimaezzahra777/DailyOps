# DailyOps

DailyOps est une application web de gestion opérationnelle construite avec Laravel. Elle centralise le suivi des projets, des tâches, des réunions, des fichiers de création, des utilisateurs et des échanges de support client.

L'objectif du projet est de fournir un espace de travail clair pour les managers et les membres d'équipe: planification, suivi d'avancement, collaboration, notifications et communication client autour de chaque projet.

## Sommaire

- [Fonctionnalités principales](#fonctionnalites-principales)
- [Stack technique](#stack-technique)
- [Prérequis](#prerequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Commandes utiles](#commandes-utiles)
- [Architecture du projet](#architecture-du-projet)
- [Modèle de données](#modele-de-donnees)
- [Rôles et permissions](#roles-et-permissions)
- [Flux support client](#flux-support-client)
- [Emails et notifications](#emails-et-notifications)
- [Tests](#tests)
- [Déploiement](#deploiement)

## Fonctionnalités principales

### Gestion des projets

- Création, modification, suppression et consultation des projets.
- Attribution automatique du manager au créateur du projet.
- Gestion des informations projet: nom, description, entreprise, logo, email client, statut, dates de début et de fin.
- Statuts projet:
  - `pending`: Cahier charge
  - `in_progress`: Développement
  - `testing`: Teste
  - `completed`: Déploiement
- Archivage automatique des projets terminés après 5 jours.
- Restauration des projets archivés.
- Vues multiples:
  - Kanban
  - Tableau
  - Gantt
  - Calendrier
  - Rapports
  - Archives

### Colonnes personnalisées

- Création de colonnes de projet propres à chaque utilisateur.
- Déplacement des projets entre les colonnes ou entre les statuts.
- Isolation des colonnes personnalisées par propriétaire.

### Gestion des tâches

- Création, modification, consultation et suppression des tâches.
- Association des tâches à un projet.
- Changement de statut des tâches.
- Attribution des tâches à un utilisateur visible sur le projet.
- Commentaires sur les tâches.
- Pièces jointes avec upload, téléchargement, prévisualisation et suppression.

### Collaboration projet

- Invitation de collaborateurs par email.
- Acceptation ou refus d'une invitation projet.
- Ajout automatique d'un utilisateur comme collaborateur après acceptation.
- Visibilité des projets selon le rôle, le manager, les collaborateurs ou l'affectation.

### Réunions

- Création de réunions avec lien, date et participants.
- Affichage des réunions dans le calendrier.
- Recherche dans la liste des réunions.
- Permissions limitées à l'organisateur pour la modification et la suppression.
- Visibilité des réunions pour l'organisateur et les participants.

### Support client

- Page publique `/dailyops/support`.
- Formulaire client: nom, prénom, email, téléphone, titre du chat et description du problème.
- Vérification de l'email client avec le champ `client_email` des projets.
- Création d'une conversation support temporaire si l'email correspond à un projet.
- Chat entre le client et le manager du projet.
- Expiration automatique du chat après 48h.
- Espace manager pour consulter et répondre aux conversations support.

### Gestion des utilisateurs

- Authentification avec Laravel Breeze.
- Inscription, connexion, déconnexion, mot de passe oublié et réinitialisation.
- Gestion du profil utilisateur.
- Préférences d'apparence:
  - couleur d'accent
  - taille de police
- Administration des utilisateurs par les admins.
- Rôles:
  - `admin`
  - `member`

### Créations et fichiers

- Espace `Créations` pour regrouper les fichiers liés aux projets.
- Visibilité limitée aux fichiers des projets accessibles à l'utilisateur.

### Rappels anniversaire

- Date de naissance sur les profils utilisateur.
- Commande de rappel pour envoyer un email lorsqu'un anniversaire arrive le lendemain.
- Gestion du cas du 29 février sur les années non bissextiles.

## Stack technique

### Backend

- PHP `^8.3`
- Laravel `^13.7`
- Laravel Breeze pour l'authentification
- Laravel Reverb pour le temps réel
- Laravel Queue pour les traitements asynchrones
- Eloquent ORM
- PHPUnit `^12.5`

### Frontend

- Blade
- Tailwind CSS `^3.4`
- Vite `^8.0`
- Alpine.js
- Tabler Icons
- Material Symbols

### Outils

- Composer
- npm
- Laravel Pint
- Laravel Pail
- Concurrently

## Prérequis

Avant d'installer le projet, vérifier que les outils suivants sont disponibles:

- PHP 8.3 ou supérieur
- Composer
- Node.js et npm
- Une base de données compatible Laravel, par exemple MySQL, MariaDB ou SQLite
- Git

## Installation

Cloner le projet, puis installer les dépendances:

```bash
composer install
npm install
```

Créer le fichier d'environnement:

```bash
cp .env.example .env
php artisan key:generate
```

Configurer la base de données dans `.env`, puis lancer les migrations:

```bash
php artisan migrate
```

Optionnellement, exécuter les seeders:

```bash
php artisan db:seed
```

Compiler les assets:

```bash
npm run build
```

Lancer l'application en développement:

```bash
composer run dev
```

Cette commande démarre en parallèle:

- le serveur Laravel
- Reverb
- le worker de queue
- les logs avec Pail
- Vite

## Configuration

Les principales variables à vérifier dans `.env` sont:

```env
APP_NAME=DailyOps
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

ADMIN_NAME="DailyOps Admin"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dailyops
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
```

Avant d'exécuter les seeders en production, définir obligatoirement `ADMIN_PASSWORD`.
Sans cette variable, `AdminSeeder` bloque la création du premier admin afin d'éviter un compte inaccessible avec un mot de passe généré aléatoirement.

Pour utiliser les uploads de fichiers et logos projet, créer le lien symbolique de stockage:

```bash
php artisan storage:link
```

## Commandes utiles

### Développement

```bash
composer run dev
```

### Serveur Laravel seul

```bash
php artisan serve
```

### Frontend avec Vite

```bash
npm run dev
```

### Build frontend

```bash
npm run build
```

### Migrations

```bash
php artisan migrate
```

### Tests

```bash
php artisan test
```

### Nettoyage cache

```bash
php artisan optimize:clear
```

### Formatage PHP

```bash
./vendor/bin/pint
```

## Architecture du projet

```text
app/
  Http/Controllers/       Contrôleurs web
  Models/                 Modèles Eloquent
  Services/               Logique métier applicative
  Repositories/           Accès aux données pour certains modules
  Mail/                   Emails applicatifs
  Events/                 Événements temps réel
  Console/Commands/       Commandes Artisan personnalisées

database/
  migrations/             Structure de la base de données
  seeders/                Données initiales
  factories/              Factories de tests

resources/
  views/                  Interfaces Blade
  css/                    Styles Tailwind
  js/                     JavaScript applicatif

routes/
  web.php                 Routes principales
  auth.php                Routes d'authentification
  channels.php            Canaux de broadcasting

tests/
  Feature/                Tests fonctionnels
  Unit/                   Tests unitaires
```

## Modèle de données

### Entités principales

- `users`: utilisateurs, rôles, préférences et profil.
- `projects`: projets, manager, entreprise, logo, email client, statut et dates.
- `project_columns`: colonnes personnalisées du tableau projet.
- `project_user`: relation collaborateurs-projets.
- `project_invitations`: invitations envoyées aux collaborateurs.
- `tasks`: tâches liées aux projets.
- `task_columns`: colonnes des tâches.
- `comments`: commentaires de tâches.
- `task_attachments`: pièces jointes des tâches.
- `meetings`: réunions.
- `meeting_user`: participants des réunions.
- `creadations`: fichiers de l'espace créations.
- `support_conversations`: conversations support client.
- `support_messages`: messages des conversations support.

### Relations importantes

- Un projet appartient à un manager (`manager_id`).
- Un projet peut avoir plusieurs collaborateurs.
- Un projet peut avoir plusieurs tâches.
- Une tâche peut avoir plusieurs commentaires et pièces jointes.
- Une réunion appartient à un organisateur et peut avoir plusieurs participants.
- Une conversation support appartient à un projet et à son manager.
- Une conversation support contient plusieurs messages.

## Rôles et permissions

### Admin

Un administrateur peut:

- accéder à tous les projets;
- gérer les utilisateurs;
- consulter les projets archivés;
- voir les conversations support;
- accéder aux fonctionnalités globales de l'application.

### Member

Un membre peut:

- voir les projets qu'il gère;
- voir les projets où il est collaborateur;
- voir les projets qui lui sont assignés;
- gérer ses propres colonnes;
- gérer les tâches des projets visibles;
- consulter les conversations support des projets dont il est manager.

## Flux support client

Le support client fonctionne sans authentification client.

1. Le client ouvre `/dailyops/support`.
2. Il remplit le formulaire avec:
   - nom;
   - prénom;
   - email;
   - téléphone;
   - titre du chat;
   - description du problème.
3. DailyOps normalise l'email et cherche un projet dont `client_email` correspond.
4. Si aucun projet n'est trouvé, le formulaire affiche une erreur.
5. Si un projet est trouvé, DailyOps crée une `SupportConversation`.
6. Un premier `SupportMessage` est créé avec la description du problème.
7. Le client est redirigé vers un lien privé contenant un token.
8. Le manager du projet peut répondre depuis `Support client`.
9. La conversation expire après 48h.

Routes principales:

```text
GET  /dailyops/support
POST /dailyops/support
GET  /dailyops/support/chat/{token}
POST /dailyops/support/chat/{token}/messages
GET  /support/conversations
GET  /support/conversations/{conversation}
POST /support/conversations/{conversation}/messages
```

## Emails et notifications

DailyOps contient plusieurs emails applicatifs:

- invitation à collaborer sur un projet;
- notification d'assignation de tâche;
- mise à jour de statut projet envoyée à l'email client;
- rappel d'anniversaire.

Pour que les emails soient envoyés, configurer correctement les variables `MAIL_*` dans `.env`.

Les notifications temps réel utilisent Laravel Reverb et Laravel Echo. En développement, `composer run dev` démarre Reverb automatiquement.

## Tests

Le projet dispose d'une suite de tests fonctionnels couvrant notamment:

- authentification;
- gestion du profil;
- gestion des utilisateurs;
- projets et permissions;
- invitations projet;
- colonnes personnalisées;
- tâches et pièces jointes;
- réunions;
- créations;
- support client;
- emails et notifications.

Lancer toute la suite:

```bash
php artisan test
```

Lancer uniquement les tests support:

```bash
php artisan test tests/Feature/SupportFeatureTest.php
```

Lancer uniquement les tests projet:

```bash
php artisan test tests/Feature/ProjectFeatureTest.php
```

## Déploiement

Pour un environnement de production:

1. Installer les dépendances optimisées:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

2. Configurer `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com
```

3. Préparer Laravel:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Configurer les workers:

- queue worker pour les jobs;
- Reverb ou le service de broadcasting choisi;
- scheduler Laravel si les commandes planifiées sont utilisées.

Exemple scheduler:

```bash
* * * * * cd /chemin/vers/Daily_Ops && php artisan schedule:run >> /dev/null 2>&1
```

## Qualité et maintenance

Bonnes pratiques recommandées:

- lancer les tests avant chaque livraison;
- vérifier les migrations avant production;
- garder les rôles et permissions cohérents;
- ne pas exposer les tokens de chat support publiquement;
- configurer correctement les emails avant d'utiliser les notifications client;
- sauvegarder régulièrement la base de données et le disque de stockage.

## Licence

Ce projet est basé sur Laravel. La licence applicative peut être définie selon les règles internes de l'équipe ou de l'organisation.
