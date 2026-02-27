# Questions / Réponses possibles – Validation PIDEV (Maternia)

Ce document liste des questions que le professeur pourrait poser, avec des réponses courtes et précises basées sur le code du projet.

---

## 1. Bundles externes

**Q : Quels bundles externes avez-vous intégrés et comment les avez-vous personnalisés ?**

**R :** On utilise notamment :
- **KnpPaginatorBundle** : pagination de la liste des produits sur la marketplace. Il est personnalisé dans `config/packages/knp_paginator.yaml` avec le thème Bootstrap 5 (`bootstrap_v5_pagination.html.twig`) et les noms de paramètres (`page`, `sort`, `direction`). Dans `HomeController::marketplace()`, on passe un QueryBuilder au paginator avec `$paginator->paginate($qb, $page, 8)`, et dans le template on affiche les liens avec `knp_pagination_render(pagination)`.
- **DoctrineFixturesBundle** : pour charger des données de test. Les `ProduitFixtures` ont un groupe `produit` ; on lance `doctrine:fixtures:load --group=produit` pour avoir des produits avec catégories (bebe, grossesse, soins, etc.) sans toucher au reste.
- **Twilio SDK** (via Composer) : dans `NotificationService`, on envoie un SMS de confirmation après commande si les variables Twilio sont configurées dans le `.env`.

---

**Q : Où est utilisée la pagination KnpPaginator dans le projet ?**

**R :** Dans la **marketplace publique** (`/marketplace`), dans `HomeController::marketplace()`. On récupère les paramètres `q` (recherche), `categorie` et `page`, on construit un QueryBuilder via `ProduitRepository::getQbByCategorieAndSearch()`, puis on appelle `$paginator->paginate($qb, $page, 8)`. Le template reçoit l’objet `pagination` et utilise `knp_pagination_render()` pour afficher les numéros de page en Bootstrap 5.

---

**Q : Pourquoi avoir choisi le thème Bootstrap 5 pour KnpPaginator ?**

**R :** Pour garder la même charte graphique que le reste du site (Bootstrap). Le fichier de config est `config/packages/knp_paginator.yaml` avec `pagination: '@KnpPaginator/Pagination/bootstrap_v5_pagination.html.twig'`.

---

## 2. Entités et modèle de données

**Q : Décrivez l’entité Produit et ses champs principaux.**

**R :** `Produit` a notamment : `id`, `nom`, `description`, `prix`, `stock`, `categorie` (slug : bebe, grossesse, soins, mode, equipement, services), `imageName` (fichier dans `public/uploads/produits/`), `poidsKg` (pour le calcul livraison), `sku`, `ratingAverage`, `ratingCount`. Il y a une relation **ManyToMany** avec `Commande` : un produit peut être dans plusieurs commandes, une commande contient plusieurs produits. Les validations (Assert) sont sur le nom, la description, le prix, le stock, le poids.

---

**Q : Quelle est la relation entre Commande et Produit ? Comment est-elle gérée en base ?**

**R :** Relation **ManyToMany** : une commande peut contenir plusieurs produits, un produit peut apparaître dans plusieurs commandes. En base, une table de jointure `commande_produit` contient les paires `commande_id` et `produit_id`. Côté `Commande`, la propriété `produits` est une Collection avec `addProduit()` / `removeProduit()` ; côté `Produit`, la propriété `commandes` est en `mappedBy` pour la relation inverse.

---

**Q : Quels champs de Commande sont utilisés pour la livraison et le paiement ?**

**R :** Livraison : `shippingAddress`, `shippingCity`, `shippingPostalCode`, `shippingCountry`, `shippingCost`, `shippingCarrier`, `shippingEtaDays`, `shippingTracking`. Contact : `email`, `telephone`. Paiement : `paymentStatus` (ex. `pending_offline`), `paidAt`. Plus `total`, `dateCommande`, `statut` (En attente, Validée, Annulée).

---

## 3. Marketplace, filtrage, recherche

**Q : Comment fonctionne le filtrage par catégorie et la recherche sur la marketplace ?**

**R :** Les paramètres GET sont `categorie` et `q`. Dans `HomeController::marketplace()`, on les récupère et on appelle `ProduitRepository::getQbByCategorieAndSearch($categorie, $term)`. Cette méthode construit un QueryBuilder : si `categorie` est renseigné, on ajoute `p.categorie = :cat` ; si `q` (recherche) est renseigné, on ajoute `p.nom LIKE :t`. Le même QueryBuilder est ensuite paginé avec KnpPaginator. Les liens « Bébé », « Grossesse », etc. pointent vers `/marketplace?categorie=bebe` (ou autre), et la recherche envoie `?q=...` (éventuellement combiné avec la catégorie).

---

**Q : Où est définie la méthode getQbByCategorieAndSearch et à quoi sert-elle ?**

**R :** Dans `ProduitRepository`. Elle retourne un **QueryBuilder** (pas le résultat) pour pouvoir le passer à KnpPaginator. Elle applique un filtre sur `categorie` si fourni, et un `LIKE` sur le nom si le terme de recherche n’est pas vide. Elle permet de réutiliser la même logique pour la liste paginée et d’éviter de dupliquer les conditions.

---

## 4. Panier et commande

**Q : Comment le panier est-il stocké ? Où est-il utilisé ?**

**R :** Le panier est stocké en **session** : une clé `cart` contient un tableau d’IDs de produits (`int[]`). Dans `CartController`, `show()` lit `$session->get('cart', [])`, `add()` ajoute l’ID au tableau et fait `$session->set('cart', $cart)`, `remove()` retire l’ID et `checkout()` après création de la commande fait `$session->remove('cart')`. Pas de table en base pour le panier.

---

**Q : Que se passe-t-il quand on ajoute un produit au panier (côté stock) ?**

**R :** Dans `CartController::add()` : on vérifie que le produit existe et qu’il n’est pas déjà dans le panier. Si on l’ajoute, on **décrémente le stock de 1** en base (`$produit->setStock($produit->getStock() - 1)`), puis on persist et flush. Quand on retire du panier (`remove`), on **incrémente le stock de 1**. Au checkout, la commande est créée mais on ne décrémente plus le stock à ce moment-là (c’est déjà fait à l’ajout).

---

**Q : Décrivez le flux complet du checkout (validation de commande).**

**R :** 1) `CartController::checkout()` reçoit un POST avec email, téléphone, adresse, ville, code postal, pays, transporteur. 2) On charge les produits du panier depuis la BDD. 3) On crée une `Commande`, on lui attache les produits et on remplit les champs livraison et contact. 4) On appelle `ShippingQuoteService::quote()` avec les produits, le pays et le transporteur pour obtenir frais et délai (DHL/Aramex si configurés, sinon formule interne). 5) On set `shippingCost`, `shippingCarrier`, `shippingEtaDays`, `total` (sous-total + frais). 6) On persist la commande et on flush. 7) On appelle `NotificationService::sendOrderPaid($commande)` pour l’email (et éventuellement SMS). 8) On vide le panier en session et on redirige vers la page de succès.

---

## 5. APIs internes et externes

**Q : Quelles APIs externes utilisez-vous et pour quoi faire ?**

**R :**
- **Open-Meteo** : météo du jour (température, code temps). Appel GET dans `WeatherApiService::getCurrentWeather()` avec latitude/longitude (par défaut Tunis). Pas de clé API. Utilisé sur la marketplace pour afficher un bloc météo/conseils.
- **OpenRouteService** : géocodage + matrice de distance pour calculer la distance en km entre l’entrepôt et l’adresse de livraison. Utilisé dans `DistanceService`. Clé dans `OPENROUTE_API_KEY`. La distance sert à la formule interne des frais (ex. 4 TND par tranche de 100 km).
- **DHL MyDHL API** : devis livraison (tarif + délai) via `DhlQuoteClient`. POST sur l’endpoint Rating, authentification Basic. Si DHL est configuré dans le `.env` et que le client choisit DHL, le devis vient de l’API.
- **Aramex** : même idée via `AramexQuoteClient` pour un endpoint REST de tarification si fourni.
- **Gmail SMTP** (Symfony Mailer) : envoi de l’email de confirmation de commande.
- **Twilio** : envoi SMS optionnel après commande si les identifiants sont renseignés.

---

**Q : Comment est appelée l’API Open-Meteo et que retourne votre service ?**

**R :** `WeatherApiService` utilise `HttpClientInterface`. Un GET est envoyé vers `https://api.open-meteo.com/v1/forecast` avec les paramètres `latitude`, `longitude`, `current=temperature_2m,weather_code`. On parse la réponse et on retourne un tableau avec `temperature`, `weather_code` et un `label` lisible (ex. « Ciel dégagé », « Pluie ») via une méthode `weatherCodeToLabel()`. En cas d’exception, on retourne `null` pour ne pas casser la page.

---

**Q : À quoi sert l’API interne /api/recommendations ? Comment est-elle consommée ?**

**R :** Elle retourne en JSON une liste de produits recommandés selon le profil. Paramètres GET : `age_bebe`, `trimestre`, ou `popular`. La logique (règles métier « IA ») : si `age_bebe` → catégories bebe et soins ; si `trimestre` ≤ 2 → grossesse et soins, sinon equipement et bebe ; si `popular=true` → produits les plus commandés (via `CommandeRepository::topProduitsCommandes()`). Sinon fallback sur les derniers produits. Elle est consommée en **JavaScript** depuis la page marketplace (section « Recommandations pour vous »), par exemple avec un fetch vers `/api/recommendations?trimestre=2`.

---

**Q : Comment fonctionne l’API /api/shipping/quote ?**

**R :** C’est un endpoint GET dans `Api\ShippingController`. Paramètres : `weight`, `country`, `carrier` (DHL, ARAMEX, POSTE), et optionnellement `postal_code`, `city`, `address`. Le contrôleur appelle `ShippingQuoteService::quoteByWeight()`. Si le transporteur est DHL et que DHL est configuré, on utilise `DhlQuoteClient` ; si Aramex est configuré et choisi, on utilise `AramexQuoteClient`. Sinon une formule interne (base + poids + zone internationale + optionnellement distance OpenRouteService). La réponse JSON contient `carrier`, `cost`, `currency`, `etaDays`, `productCode`.

---

**Q : Comment est calculé le devis livraison si DHL et Aramex ne sont pas configurés ?**

**R :** Dans `ShippingQuoteService`, une **formule interne** : base 5 TND + 2 TND par kg, avec un coefficient selon le transporteur (DHL 1.8, Aramex 1.5, POSTE 1.0), plus un supplément zone internationale (12 TND si pays ≠ TN). Si l’adresse est renseignée et qu’OpenRouteService est configuré, on ajoute 4 TND par tranche de 100 km via `DistanceService::getDistanceInKm()`. Le délai estimé (`etaDays`) est 2 jours en TN, 5 à l’international, et peut être augmenté selon la distance.

---

## 6. IA et recommandations

**Q : Où est l’intégration de l’IA dans le projet ?**

**R :** Elle est matérialisée par la **logique de recommandation intelligente** dans `RecommendationController`. Selon le profil (âge du bébé ou trimestre de grossesse), on choisit des catégories prioritaires et on retourne les produits correspondants, avec un fallback (produits récents ou par prix, ou produits les plus commandés si `popular=true`). C’est une approche par **règles métier** plutôt qu’un appel à une API de type ChatGPT/OpenAI ; on peut la présenter comme une « IA symbolique » ou une personnalisation pilotée par des règles.

---

**Q : Comment sont déterminés les « produits populaires » dans l’API recommandations ?**

**R :** Quand le paramètre `popular` est à true, on appelle `CommandeRepository::topProduitsCommandes(4)`. Cette méthode exécute une requête SQL sur la table de jointure `commande_produit` en ne gardant que les commandes avec le statut « Validée », on groupe par produit (nom), on compte le nombre de commandes et on trie par ce nombre décroissant, avec une limite (4). On récupère les noms des produits puis on charge les entités Produit correspondantes pour les retourner en JSON.

---

## 7. Services et architecture

**Q : Quel est le rôle du NotificationService ?**

**R :** Il envoie la notification après une commande : **email** de confirmation (template Twig `emails/commande_payee.html.twig`, sujet avec le numéro de commande) via Symfony Mailer, et **SMS** optionnel via Twilio si les variables `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER` sont définies. L’adresse d’expéditeur pour l’email vient de `MAILER_FROM` (injecté dans le service). En cas d’échec d’envoi d’email, l’erreur est loguée pour ne pas bloquer le checkout.

---

**Q : Pourquoi avoir un ShippingQuoteService en plus des clients DHL et Aramex ?**

**R :** Pour **orchestrer** la logique : selon le transporteur choisi et la configuration (.env), on décide d’appeler DHL, Aramex ou la formule interne. Le service agrège aussi le calcul du poids à partir des produits, l’appel éventuel à `DistanceService`, et retourne toujours un objet `ShippingQuote` (coût, délai, transporteur) de façon uniforme pour le checkout et l’API.

---

**Q : Où sont injectées les variables d’environnement (clés API, etc.) ?**

**R :** Dans `config/services.yaml` : les services `DistanceService`, `DhlQuoteClient`, `AramexQuoteClient` et `NotificationService` reçoivent leurs paramètres via `arguments` avec `%env(NOM_VARIABLE)%`. Les valeurs viennent du fichier `.env` (ou `.env.local`). Ainsi on ne met pas de secrets dans le code et on peut changer la config par environnement.

---

## 8. Admin et export

**Q : Comment fonctionne l’export CSV des produits en admin ?**

**R :** Dans `AdminProduitController::exportCsv()`, on utilise une `StreamedResponse` : on ouvre un flux en écriture vers `php://output`, on écrit une ligne d’en-tête (ID, Nom, Description, Prix, Stock) avec `fputcsv` (séparateur `;`), puis pour chaque produit retourné par `findAll()` on écrit une ligne. Les en-têtes HTTP indiquent `Content-Type: text/csv` et `Content-Disposition: attachment` avec un nom de fichier contenant la date. Pas de chargement complet en mémoire de tout le CSV, c’est du streaming.

---

**Q : Comment est géré l’upload d’image pour un produit en admin ?**

**R :** Le formulaire `ProduitType` contient un champ `imageFile` (FileType). Dans `AdminProduitController`, après `$form->isValid()`, on appelle `handleImageUpload()` : on récupère le fichier, on génère un nom sécurisé avec le Slugger (nom + uniqid + extension), on enregistre le fichier dans `public/uploads/produits/` et on set `$produit->setImageName($newFilename)`. L’entité stocke seulement le nom du fichier ; le fichier physique est dans le répertoire public.

---

## 9. Sécurité et validation

**Q : Comment protégez-vous les formulaires (CSRF) ?**

**R :** Les formulaires Symfony génèrent automatiquement un token CSRF. Pour la suppression d’un produit en admin, on vérifie explicitement le token avec `$this->isCsrfTokenValid('delete_admin_produit'.$produit->getId(), $request->request->get('_token'))`. Si le token est invalide, on n’exécute pas la suppression et on affiche un message d’erreur.

---

**Q : Y a-t-il une restriction d’accès pour l’admin ?**

**R :** Dans le projet actuel, la configuration dans `security.yaml` montre un provider `users_in_memory` avec `memory: null` et les règles `access_control` pour `/admin` sont commentées. Donc actuellement l’admin n’est pas protégé par un login ; pour une vraie mise en production il faudrait activer une authentification (form_login ou autre) et exiger `ROLE_ADMIN` sur les routes `/admin`.

---

**Q : Où sont définies les validations sur les entités ?**

**R :** Dans les entités avec les attributs **Assert** de `Symfony\Component\Validator\Constraints` : par exemple sur `Produit` : `NotBlank`, `Length` sur le nom, `Positive` sur le prix, `PositiveOrZero` sur le stock, etc. Sur `Commande` : `Email` sur l’email, `Choice` sur le statut, etc. Lors d’un `$form->handleRequest()` puis `$form->isValid()`, le validateur Symfony vérifie ces contraintes avant de persister.

---

## 10. Fixtures et scénario de test

**Q : Comment charger les données de test pour la démo ?**

**R :** Avec Doctrine Fixtures : `php bin/console doctrine:fixtures:load --group=produit`. Les `ProduitFixtures` ont `getGroups()` qui retourne `['produit']`. Elles créent des produits avec des catégories (grossesse, bebe, soins, mode, equipement, services) et peuvent associer une image depuis `public/img/` si le fichier existe. Avec `--append`, on évite d’écraser les produits existants (pas de doublon par nom).

---

**Q : Quel enchaînement logique proposez-vous pour la démo ?**

**R :** Voir `SCENARIO.md` : 1) Ouvrir la marketplace, montrer les catégories. 2) Filtrer par catégorie (ex. Bébé) et éventuellement la recherche. 3) Montrer la pagination (KnpPaginator). 4) Montrer la section Recommandations (appel API `/api/recommendations`) et éventuellement l’onglet Réseau. 5) Montrer le bloc météo (API Open-Meteo). 6) Expliquer la logique IA/recommandations. 7) Aller en admin : liste, recherche, tri, export CSV, édition d’un produit (catégorie, image). 8) Panier : ajout, checkout avec email, confirmation et réception de l’email.

---

## 11. Configuration et déploiement

**Q : Comment le projet sait-il quelle base de données utiliser ?**

**R :** Via la variable d’environnement `DATABASE_URL` dans le `.env` (format DSN Doctrine, ex. `mysql://user:password@127.0.0.1:3306/maternia?serverVersion=...`). Doctrine lit cette URL dans `config/packages/doctrine.yaml` pour la connexion et les mappings des entités.

---

**Q : Pourquoi les e-mails sont envoyés de façon synchrone et pas en file ?**

**R :** Dans `config/packages/messenger.yaml`, les messages `SendEmailMessage` sont routés vers le transport `sync` (au lieu de `async`). Ainsi l’email est envoyé immédiatement dans le même processus que le checkout, sans avoir besoin de lancer un worker `messenger:consume`. On a choisi ça pour simplifier la démo et s’assurer que l’utilisateur reçoit bien l’email tout de suite.

---

## 12. GitHub et travail collaboratif

**Q : Comment avez-vous intégré le travail sur une seule machine avec GitHub ?**

**R :** (À adapter à votre pratique.) On utilise des branches pour chaque feature ou tâche, on pousse les commits sur le dépôt distant, et on fait des merge (pull request ou merge local) vers la branche principale pour consolider le travail. Tout est poussé avant la séance de validation pour que le dépôt soit à jour ; sans dépôt à jour, aucune réclamation n’est possible selon le sujet.

---

**Q : Où est documentée la configuration des APIs (DHL, Aramex, etc.) ?**

**R :** Dans `docs/SHIPPING_APIS.md` et `docs/CONFIG_DHL_ARAMEX.md` (si présent). Le `.env` contient les variables (DHL_*, ARAMEX_*, OPENROUTE_API_KEY, MAILER_DSN, TWILIO_*, etc.) et des commentaires dans le fichier ou dans la doc expliquent où obtenir les clés et comment les renseigner.

---

*Document généré pour la préparation à la validation PIDEV – Maternia. Adapter les réponses à votre propre expérience et à vos choix techniques.*
