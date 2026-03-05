# Checklist PIDEV – Sprint WEB (semaine du 23/02/2026)

**Vérification détaillée** : voir `docs/VERIF_PIDEV_23022026.md` pour le rapport complet (bundles, APIs, métier, IA, GitHub, scénario).

---

## Critères notés et correspondance dans le projet Maternia

| # | Critère | Élément dans le projet | Statut / Fichiers clés |
|---|--------|------------------------|-------------------------|
| 1 | **Fonctionnalités métiers avancées** | Marketplace (produits, catégories, panier, commandes), filtrage par catégorie, recherche, export CSV admin, pagination, email confirmation commande | ✅ Controllers : `HomeController`, `CartController`, `CommandeController`, `AdminProduitController` ; Entités : `Produit`, `Commande` ; `NotificationService` |
| 2 | **Bundle(s) externe(s) intégré(s) et personnalisé(s)** | KnpPaginator (marketplace + admin, thème Bootstrap 5), Doctrine Fixtures (groupe `produit`), Symfony UX (Stimulus/Turbo), Twilio SDK (SMS) | ✅ `config/packages/knp_paginator.yaml` ; `ProduitFixtures` ; `NotificationService` |
| 3 | **Intégration d’APIs** | API interne `/api/recommendations` et `/api/shipping/quote` ; APIs externes : Open-Meteo (météo), OpenRouteService (distance), DHL MyDHL (devis), Aramex (devis), Gmail SMTP, Twilio (SMS) | ✅ `RecommendationController`, `Api\ShippingController` ; `WeatherApiService`, `DistanceService`, `DhlQuoteClient`, `AramexQuoteClient`, `NotificationService` |
| 4 | **Maîtrise du sujet et qualité de l’argumentation** | Comprendre le code (entités, services, contrôleurs, formulaires) et savoir expliquer les choix | À préparer : voir `VERIF_PIDEV_23022026.md` section 7 |
| 5 | **Quantité de travail / valeur ajoutée** | Marketplace complète, catégories, panier, commandes, recommandations, météo, livraison multi-transporteurs, email/SMS | Renforcer avec scénario et données de test |
| 6 | **Plateforme collaborative + GitHub (merge sur une machine)** | Dépôt GitHub à jour, branches/merge effectués | ⚠️ **À faire avant la séance** : push, merge, dépôt prêt pour validation. Aucune réclamation possible si non déposé. |

---

## Intégration de l’IA

- **Recommandations** : `RecommendationController` – logique de recommandation **intelligente** selon `trimestre` / `age_bebe` (règles métier) :
  - Si `age_bebe` : priorité catégories **bebe** et **soins**.
  - Si `trimestre` ≤ 2 : priorité **grossesse** et **soins** ; sinon **equipement** et **bebe**.
  - Fallback : produits récents ou populaires (top commandes).
- L’API `/api/recommendations` est consommée en JavaScript sur la page marketplace (section « Recommandations pour vous »).
- **Option** : pour renforcer le critère « intégration de l’IA », ajouter un appel à une API IA (ex. OpenAI) pour un résumé de produit ou un conseil personnalisé, avec fallback si clé non configurée. Voir `VERIF_PIDEV_23022026.md` section 4.

---

## À faire avant la séance

1. **GitHub** : tout le travail déposé et intégré (merge) sur la branche demandée ; **aucune réclamation possible si le dépôt n’est pas à jour**.
2. **Scénario** : préparer un enchaînement logique avec des données de test (voir `SCENARIO.md` et `php bin/console doctrine:fixtures:load --group=produit`).
3. **Réponses sur le code** : revoir les parties listées dans `VERIF_PIDEV_23022026.md` pour pouvoir expliquer clairement le fonctionnement.
