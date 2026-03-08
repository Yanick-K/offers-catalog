# Test technique senior — HelloCSE (Laravel)

Bienvenue ! Ce dépôt sert de base à un test technique destiné à un·e développeur·se senior PHP/Laravel.
Votre mission est d’améliorer techniquement l’application existante autour de la gestion d’offres et de produits.

## Objectif général

- Apporter des améliorations structurelles et de qualité au projet (architecture, tests, qualité de code) tout en conservant le fonctionnement existant.
- L’enjeu est d’évaluer votre capacité à raisonner, structurer, sécuriser et tester un code Laravel dans un contexte proche de la production.

## Contenu actuel du projet (à connaître)
- Back-office simple de gestion d’offres et des produits liés à une offre.
- API publique GET /api/offers retournant uniquement les offres et produits publiés.

## Ce que nous attendons (périmètre minimal)

- Temps indicatif réalisation : 3 à 8 heures.
- Pas de sur-investissement UI/Design. Restez focalisé sur la qualité backend et l’ingénierie.
- Préférez des améliorations progressives et pragmatiques à une réécriture totale.

1) Architecture et séparation des responsabilités
   - Extraire le code métier dans des services/domain pour découpler la couche HTTP de la logique métier.
   - Introduire si nécessaire des classes dédiées (ex: Actions/Services, DTO, Repositories, Query Objects) avec un design clair, testable et documenté.

2) Qualité de code et outillage
   - PHPStan niveau 8 minimum (viser 9 si pertinent) et correction des erreurs remontées.
   - Ajouter/Configurer d’autres outils que vous jugez pertinents (ex: Larastan, PHP-CS-Fixer/Pint, Psalm, Laravel Pint, Rector) avec une configuration minimale et reproductible.
   - Respect des conventions (PSR-12, nommage, règles de complexité raisonnables, petites méthodes, dépendances explicites).

3) Tests
   - Écrire des tests unitaires PHPUnit ciblant la logique métier extraite (services, règles d’état, validations métiers, etc.).
   - Ajouter des tests de feature pertinents (ex: endpoints, règles d’accès, flux critiques).
   - Viser une couverture utile et significative sur les parties clés (pas de « test pour tester »).

4) Données & démos
   - Ajouter des seeders pour fournir un jeu de données de démonstration cohérent (offres + produits, états variés, images simulées si besoin).
   - Veiller à ce que l’appli soit rapidement exploitable après installation (un développeur doit voir une UI et des données en quelques commandes).

5) Robustesse
   - Gestion propre des validations (FormRequest, règles partagées, messages clairs).
   - Gestion des fichiers (images) sécurisée et robuste.
   - Pagination, tri et filtres côté back si nécessaire pour la scalabilité.
   - API Resources/Transformers pour les réponses API (contract stable, filtrage des champs, sérialisation).

6) Documentation
   - Architecture et décisions clés
   - Comment lancer tests et outils
   - Comment naviguer dans le code

## Bonus appréciés (optionnels, choisissez selon le temps / pertinence)

- Patterns avancés (DDD light, Ports/Adapters, Repositories, Query Services, Specification, Value Objects).
- Extraire la logique liée aux états (transitions possibles, règles d’affichage, filtrages par défaut)
- Politique de sécurité (Policies/Gates), middleware d’auth, rate limiting, validation d’input stricte.
- Documentation API (OpenAPI/Swagger), versionnement API, pagination/tri/filtrage RESTful.
- Optimisations perfs (index DB, N+1, caches, Eager Loading par défaut, Scopes).
- CI (GitHub Actions) exécutant lint + static analysis + tests.
- Makefile ou scripts pour simplifier les commandes.
- Observers, Events/Listeners, Notifications, Queues (jobs pour traitement d’images par ex.).

## Critères d’évaluation
- Clarté de l’architecture, découpage des responsabilités, lisibilité.
- Qualité des tests (pertinence, couverture utile, isolation, fidélité à la logique métier).
- Niveau de qualité de code (typages, immutabilité quand pertinent, complexité maîtrisée, cohérence globale, commentaires ciblés).
- Robustesse des choix techniques (validation, gestion des états, gestion fichiers, erreurs, sécurité basique).
- Expérience de dev et reproductibilité (setup simple, scripts, doc, seeders, cohérence des environnements).
- Pertinence des bonus si présents (pas nécessaire d’en faire beaucoup; qualité > quantité).

## Consignes de rendu
- Travaillez dans une branche dédiée et ouvrez une Pull Request (ou fournissez un patch) expliquée clairement.
- Commits atomiques et messages explicites.
- Ajoutez/éditez ce README pour décrire vos choix techniques: architecture, services, tests, outillage, limites connues et pistes d’amélioration.
- Si vous ajoutez d’autres outils (Pint, Psalm, Rector…), documentez les commandes dans ce README ou un Makefile.
- Indiquez le temps passé et ce que vous auriez fait avec plus de temps.

## Questions
Si un point n’est pas clair, documentez vos hypothèses directement dans la PR/README et avancez. Vous pouvez proposer des alternatives techniques et expliquer vos arbitrages.

Bon courage et merci !

## Environnement et installation
Prérequis
- PHP 8.4+
- Composer 2
- Node 18+ et npm
- MySQL/MariaDB (ou SQLite si vous préférez pour l’exercice)

Étapes rapides (local)
1. Copier l’environnement
   - cp .env.example .env
   - Configurer la base de données (DB_*) et le stockage local.
2. Installer et initialiser
   - make setup
3. Builder les assets (si UI utilisée)
   - npm run build (ou npm run dev pour le watch)
4. Lancer l’application
   - make dev

Tests et qualité
- Migrations (si besoin): php artisan migrate
- Lancer les tests: php artisan test
- Lancer PHPStan: vendor/bin/phpstan analyse
- Lancer Deptrac: vendor/bin/deptrac analyse --config-file=deptrac.yaml
- Lancer Pint (lint): vendor/bin/pint --test

## Notes techniques (ajouts)

### Architecture et séparation
- DDD light Domain/Application/Infrastructure avec deptrac pour verrouiller les dépendances.
- Choix assumé pour isoler le domaine des détails Eloquent et garder des frontières testables, tout en restant léger vu la simplicité métier.
- Domain: Entities, ValueObjects, Queries et interfaces de repository (`App\Domain\...`).
- Application: DTOs + services d'usage (`App\Application\Offers\Services\OfferService`, `App\Application\Products\Services\ProductService`).
- Infrastructure: repositories Eloquent + cache (`EloquentOfferRepository`, `PublicOfferCache`) + `StorageImageUploader`.
- Shared: port `ImageUploader` pour découpler l'upload d'images.

### Validations et robustesse
- FormRequests dédiés (create/update/index) avec règles explicites.
- Service d'upload image isolé (`ImageUploader`) et suppression des anciens fichiers via observers.
- Pagination ajoutée sur le back-office et l'API; tri côté back via `sort`/`direction`.
- Prix gérés via `Money` (centimes) dans le domaine, convertis pour la persistance et l'affichage.

### Droits et sécurité
- Gate `admin` basé sur `users.is_admin` pour restreindre le back-office.
- Compte démo admin: `test@example.com` / `password`.

### Observers et audits
- `OfferObserver` et `ProductObserver` pour la gestion des fichiers et l'invalidation du cache.
- Table `audit_logs` pour tracer create/update/delete (user + changements).
- Les anciens fichiers d'image sont supprimés par les observers (tests: `ImageCleanupTest`, `OfferServiceTest`, `ProductServiceTest`).

### Performance
- Index sur `offers.state` et `products.state`.
- Cache versionné pour `GET /api/v1/offers`.

### API
- API Resources (`OfferResource`, `ProductResource`) pour stabiliser le contrat.
- API versionnée: `GET /api/v1/offers` (et alias legacy `GET /api/offers`).
- OpenAPI/Swagger: `/docs` (spec dans `public/docs/openapi.yaml`).
- `GET /api/v1/offers` paginé, accepte `per_page` (1-100) et retourne uniquement les offres/produits publiés.

### Seeders
- `OfferSeeder` crée un jeu de données cohérent (offres + produits, états variés) avec images locales.
- Commande `demo:seed --offers=10 --products=5` pour injecter un volume de données à la volée.
- Option images distantes: `demo:seed --offers=10 --products=5 --remote`.
- Sans options, `demo:seed` passe en mode interactif.

### Tests ajoutés
- Unit: `OfferServiceTest`, `ProductServiceTest`.
- Feature: `OfferManagementTest`, `Api/OfferIndexTest`.

### Outillage
- Larastan / PHPStan niveau 8 (`phpstan.neon`).
- Deptrac pour vérifier les dépendances de couches (`deptrac.yaml`).
- Laravel Pint déjà présent: `vendor/bin/pint`.
- Dépendances PHP mises à jour pour corriger des vulnérabilités (composer audit OK).

### Commandes de base (local)
- `make setup`
- `make reset`
- `make dev`
- `make dev-detach`
- `make dev-stop`
- `make lint`
- `make test-all` (ou `make ci`)
- `make seed OFFERS=10 PRODUCTS=5` (ou `make seed-base`)
- `make seed-remote OFFERS=10 PRODUCTS=5`

Plus d'options dans `tools/README.md`.

### CI
- GitHub Actions: lint (Pint) + PHPStan + Deptrac + tests.

### Hooks git
- Activer le hook pre-commit: `git config core.hooksPath .githooks` (puis `pint --test` ou `php-cs-fixer` sont lancés au commit).

### Temps passé
- Environ 7-8 heures.

### Avec plus de temps
- Rôles/permissions plus fines (owner, équipes, multi-tenancy).
- Audit UI + exports (CSV) et politique de rétention.
- Cache distribué avec tags + warmup.
- Docker/Sail pour des environnements homogènes.
