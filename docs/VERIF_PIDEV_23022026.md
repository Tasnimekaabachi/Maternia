# Vérification PIDEV – Sprint WEB (semaine du 23/02/2026)

## Critères demandés vs état du projet Maternia

| Critère | Exigence | État dans Maternia | Statut |
|--------|----------|--------------------|--------|
| 1 | **Bundle(s) externe(s) intégré(s) et personnalisé(s)** | Au moins un bundle externe, configuré/adapté au projet | Voir détail ci‑dessous | ✅ |
| 2 | **Intégration des APIs** | APIs externes et/ou internes utilisées dans l’app | Voir détail ci‑dessous | ✅ |
| 3 | **Fonctionnalités métiers avancées** | Logique métier au‑delà du CRUD basique | Voir détail ci‑dessous | ✅ |
| 4 | **Intégration de l’IA** | Présence d’une dimension « IA » (recommandations, automatisation, etc.) | Voir détail ci‑dessous | ⚠️ À renforcer (voir section IA) |
| 5 | **Intégration des travaux sur une machine avec GitHub** | Dépôt à jour, branches/merge | À faire avant la séance | ⚠️ À faire |
| 6 | **Scénario + données de test** | Enchaînement logique, données reproductibles | SCENARIO.md + fixtures | ✅ |
| 7 | **Réponses sur le code** | Savoir expliquer le code | À préparer | À préparer |

---

## 1. Bundle(s) externe(s) – ✅ OK

- **KnpPaginatorBundle** (`knplabs/knp-paginator-bundle`)
  - **Où** : marketplace (liste produits paginée), admin (liste produits paginée).
  - **Personnalisation** : `config/packages/knp_paginator.yaml` – thème Bootstrap 5 (`bootstrap_v5_pagination.html.twig`), noms des options (`page`, `sort`, `direction`).
  - **Fichiers** : `HomeController` (paginate sur le QueryBuilder), templates avec `knp_pagination_render()`.

- **DoctrineFixturesBundle** (`doctrine/doctrine-fixtures-bundle`)
  - Données de test reproductibles : `ProduitFixtures` (groupe `produit`), catégories logiques pour la démo.

- **Symfony UX (Stimulus + Turbo)** – si utilisé dans les vues (Turbo Frames, etc.), à montrer comme personnalisation front.

- **Twilio SDK** (`twilio/sdk`) – envoi SMS dans `NotificationService` (optionnel selon config).

À dire en oral : « On utilise KnpPaginator pour la pagination de la marketplace et de l’admin, avec un thème Bootstrap 5 pour garder le même style que le site. »

---

## 2. Intégration des APIs – ✅ OK

| API | Type | Rôle | Fichier / Endpoint |
|-----|------|------|--------------------|
| **Open-Meteo** | Externe | Météo du jour (contexte « sortie avec bébé ») | `WeatherApiService` – GET Open-Meteo, pas de clé |
| **OpenRouteService** | Externe | Distance km entre entrepôt et adresse livraison | `DistanceService` – geocode + matrix (clé dans `.env`) |
| **DHL MyDHL (Rating)** | Externe | Devis livraison DHL (tarif + délai) | `DhlQuoteClient` – POST `/rates`, Basic Auth |
| **Aramex (Rate)** | Externe | Devis livraison Aramex (si endpoint REST configuré) | `AramexQuoteClient` |
| **Gmail SMTP** | Externe | Envoi email confirmation commande | Symfony Mailer + `NotificationService` |
| **Twilio** | Externe | SMS optionnel après commande | `NotificationService::sendSmsIfConfigured()` |
| **/api/recommendations** | Interne | Recommandations selon trimestre / âge bébé (JSON) | `RecommendationController` – consommée en JS sur la marketplace |
| **/api/shipping/quote** | Interne | Devis livraison en JSON (poids, pays, transporteur) | `Api\ShippingController` – utilise DHL/Aramex/formule selon config |

À dire en oral : « On appelle Open-Meteo pour la météo, OpenRouteService pour la distance livraison, et l’API DHL pour les devis quand le client choisit DHL. En plus on expose deux APIs internes : recommandations et devis livraison. »

---

## 3. Fonctionnalités métiers avancées – ✅ OK

- **Marketplace** : liste produits, filtrage par **catégorie**, **recherche** texte, pagination.
- **Panier** : ajout / retrait, mise à jour stock, session.
- **Checkout** : formulaire livraison (email, tél, adresse, ville, code postal, pays, transporteur), calcul des frais (DHL / Aramex / formule interne + optionnellement distance), création `Commande`, email de confirmation (et SMS optionnel).
- **Admin** : CRUD produits, liste paginée avec recherche/tri, export CSV des commandes.
- **Recommandations** : logique selon `age_bebe` ou `trimestre` (catégories prioritaires + fallback).

Fichiers clés : `CartController`, `CommandeController`, `HomeController` (marketplace), `AdminProduitController`, `ProduitRepository::getQbByCategorieAndSearch`, `ShippingQuoteService`, `NotificationService`.

---

## 4. Intégration de l’IA – ⚠️ À clarifier / renforcer

- **Actuel** : « Recommandation intelligente » = **règles métier** dans `RecommendationController` (si `age_bebe` → catégories bébé/soins ; si `trimestre` ≤ 2 → grossesse/soins, sinon équipement/bébé ; fallback produits récents ou populaires). Pas d’appel à une API IA (type OpenAI, etc.).

- **Pour la validation** :
  - Soit vous présentez cela comme **logique métier « intelligente »** (règles explicites selon le profil) et vous insistez sur le critère « recommandations personnalisées ».
  - Soit vous ajoutez **un vrai appel à une API IA** (ex. résumé de description produit, ou génération d’un court conseil selon la catégorie) avec fallback si la clé n’est pas configurée – cela renforce clairement le critère « intégration de l’IA ».

---

## 5. GitHub – ⚠️ À faire avant la séance

- **Obligatoire** : tout le travail déposé sur GitHub **avant** la séance de validation.
- **Recommandé** : travailler sur des branches et faire des **merge** pour montrer l’intégration sur une seule machine (critère 6).
- Aucune réclamation possible si le dépôt n’est pas à jour.

---

## 6. Scénario et données de test – ✅ OK

- **SCENARIO.md** : enchaînement démo (marketplace → filtrage → pagination → API recommandations → météo → IA/recommandations → back-office → panier → commande).
- **Données** : `php bin/console doctrine:fixtures:load --group=produit` pour charger les produits avec catégories.
- **Commandes utiles** : indiquées dans SCENARIO.md.

---

## 7. Réponses sur le code – À préparer

Points à savoir expliquer (voir aussi SCENARIO.md) :

- Entités `Produit` et `Commande` (champs, relations ManyToMany).
- Filtrage marketplace : paramètres `categorie` et `q`, `ProduitRepository::getQbByCategorieAndSearch`.
- KnpPaginator : utilisation dans le contrôleur (paginate sur le QueryBuilder) et dans le template (rendu Bootstrap 5).
- APIs : rôle de `WeatherApiService`, `DhlQuoteClient`, `DistanceService` ; paramètres de `/api/recommendations` et `/api/shipping/quote`.
- Panier / checkout : session `cart`, création commande, appel `NotificationService::sendOrderPaid`, calcul frais via `ShippingQuoteService`.
- Sécurité : accès admin vs public, CSRF, validation des formulaires.

---

## Synthèse actions avant la séance

1. **GitHub** : push de tout le code, merge des branches si travail en équipe.
2. **IA** : soit bien préparer l’argument « recommandations intelligentes par règles métier », soit ajouter un appel à une API IA (ex. OpenAI) avec fallback.
3. **Scénario** : répéter l’enchaînement du SCENARIO.md avec les fixtures chargées.
4. **Oral** : parcourir les fichiers listés ci‑dessus et être capable d’expliquer chaque point en 1–2 phrases.
