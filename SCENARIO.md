# Scénario de démonstration et données de test – Maternia (PIDEV)

## Objectif

Présenter un enchaînement logique qui met en valeur : bundle externe, APIs, fonctionnalités métiers avancées et IA.

---

## Données de test recommandées

- **Produits** : chargés via les fixtures (`php bin/console doctrine:fixtures:load --group=produit`).  
  Chaque produit a une **catégorie** (ex. `bebe`, `grossesse`, `soins`, `mode`, `equipement`, `services`) pour tester le filtrage.
- **Utilisateur** : se connecter en admin (ou créer un utilisateur de test) pour gérer les produits et voir la pagination côté admin.

---

## Enchaînement proposé (scénario de démo)

1. **Accueil / contexte**  
   - Ouvrir la marketplace : `http://127.0.0.1:8000/marketplace`.  
   - Montrer les **catégories** (Grossesse, Bébé, Soins, etc.).

2. **Filtrage par catégorie (fonctionnalité métier)**  
   - Cliquer sur **Bébé** → uniquement les produits de la catégorie Bébé.  
   - Montrer le lien « Toutes les catégories » pour réinitialiser.  
   - Optionnel : utiliser la **recherche** (ex. nom d’un produit) en gardant la catégorie.

3. **Pagination (bundle externe – KnpPaginator)**  
   - Avec suffisamment de produits (fixtures), faire défiler les **pages** en bas de la liste.  
   - Montrer que l’URL et les paramètres (recherche, catégorie) sont conservés.

4. **API interne (recommandations)**  
   - Descendre jusqu’à la section « Recommandations pour vous ».  
   - Expliquer que le bloc est alimenté par l’API `/api/recommendations?trimestre=2` (appel JavaScript depuis la page).  
   - Montrer la réponse JSON (ex. via l’onglet Réseau du navigateur).

5. **API externe (ex. météo)**  
   - Montrer le bloc météo / conseils (si intégré sur la page marketplace ou ailleurs).  
   - Expliquer : appel à une API externe (ex. Open-Meteo), sans clé, pour afficher la météo du jour (contexte « sortie avec bébé »).

6. **IA (recommandations)**  
   - Expliquer que la logique dans `RecommendationController` joue le rôle de **recommandation intelligente** selon le trimestre ou l’âge du bébé (règles métier).  
   - Si une API IA externe est branchée (résumé, etc.), montrer où elle est appelée et le fallback si la clé n’est pas configurée.

7. **Back-office**  
   - Aller sur `/admin/marketplace` (ou équivalent).  
   - Montrer la liste paginée, la recherche, le tri, l’export CSV.  
   - Éditer un produit et montrer le champ **Catégorie** (personnalisation du bundle formulaire / entité).

8. **Panier et commande (fonctionnalités métiers)**  
   - Ajouter un produit au panier depuis la marketplace.  
   - Ouvrir le panier, puis passer une commande (si le flux est en place).  
   - Montrer le suivi ou la confirmation.

---

## Commandes utiles

```bash
# Charger les données de test (produits avec catégories)
php bin/console doctrine:fixtures:load --group=produit

# Vider et recharger toute la BDD (attention : efface les données)
php bin/console doctrine:fixtures:load --purge-with-truncate
```

---

## Points à savoir expliquer (questions possibles)

- **Entité `Produit`** : champs (nom, description, prix, stock, **categorie**), relation avec `Commande` (ManyToMany).
- **Filtrage marketplace** : paramètre `categorie` + `q` (recherche), méthode `getQbByCategorieAndSearch` / `findByCategorieAndSearch` dans `ProduitRepository`.
- **Bundle KnpPaginator** : utilisation dans le contrôleur (paginate sur le QueryBuilder) et dans le template (`knp_pagination_render`), thème Bootstrap 5 dans `config/packages/knp_paginator.yaml`.
- **API recommandations** : route `/api/recommendations`, paramètres `trimestre` / `age_bebe`, logique de recommandation (catégories selon profil).
- **API externe météo** : `WeatherApiService` appelle Open-Meteo (sans clé), affichage sur la marketplace.
- **IA** : logique de recommandation dans `RecommendationController` (règles métier selon trimestre / âge bébé).
- **Sécurité** : accès admin vs public, formulaire CSRF, validation des données.

---

## Rappel GitHub

- **Déposer tout le travail sur GitHub avant la séance** : aucune réclamation possible si le dépôt n’est pas à jour.
- Intégration sur une seule machine : utiliser des branches et des **merge** pour consolider le travail (critère 6).
