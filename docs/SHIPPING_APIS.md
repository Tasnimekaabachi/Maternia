# APIs Livraison (DHL, Aramex, Distance)

## Vue d’ensemble

- **Distance** : OpenRouteService (geocoding + matrix) pour calculer la distance en km entre l’entrepôt et l’adresse client. Utilisée dans la formule interne (ex. 4 TND par tranche de 100 km) quand DHL/Aramex ne sont pas utilisés.
- **DHL** : MyDHL API (Rating) pour devis temps réel (tarif + délai). Si les identifiants sont renseignés et que le client choisit DHL, le devis vient de l’API.
- **Aramex** : Client prêt pour un endpoint REST de tarification. Si activé et configuré, le devis Aramex vient de l’API.

Sans clés / sans configuration, le site utilise la **formule interne** (poids + pays + transporteur, et optionnellement distance si OpenRouteService est configuré).

---

## 1. OpenRouteService (distance)

- **Rôle** : Calcul de la distance en km entre `SHIPPING_WAREHOUSE_ADDRESS` et l’adresse de livraison (ville + code postal + pays).
- **Inscription** : [openrouteservice.org/dev/#/signup](https://openrouteservice.org/dev/#/signup) (clé gratuite).
- **.env** :
  - `OPENROUTE_API_KEY=` → ta clé API.
  - `SHIPPING_WAREHOUSE_ADDRESS="Tunis, Tunisia"` → adresse de l’entrepôt (guillemets si espaces/virgules).

Quand la distance est disponible, la formule interne ajoute **4 TND par tranche de 100 km** et peut augmenter le délai estimé.

---

## 2. DHL Express (MyDHL API)

- **Rôle** : Devis (tarif + délai) pour le transporteur DHL.
- **Doc** : [developer.dhl.com/api-reference/dhl-express-mydhl-api](https://developer.dhl.com/api-reference/dhl-express-mydhl-api).
- **Prérequis** : Compte DHL Express + accès API (Basic Auth).
- **.env** :
  - `DHL_API_BASE_URL=https://express.api.dhl.com/mydhlapi/test` (test) ou `https://express.api.dhl.com/mydhlapi` (prod).
  - `DHL_ACCOUNT_NUMBER=`
  - `DHL_USERNAME=`
  - `DHL_PASSWORD=`

Si tout est renseigné et que le client choisit DHL au checkout, le devis affiché vient de l’API DHL.

---

## 3. Aramex

- **Rôle** : Devis (tarif + délai) pour le transporteur Aramex.
- **Doc** : [dr.aramex.com – Aramex APIs](https://dr.aramex.com/ae/en/developers-solution-center/aramex-apis). L’API officielle est souvent en XML (Rate Calculator) ; ce client attend un **endpoint REST** renvoyant du JSON avec au moins `amount` (ou `totalCharge`) et optionnellement `days` (ou `deliveryDays`).
- **.env** :
  - `ARAMEX_ENABLED=1` pour activer.
  - `ARAMEX_ACCOUNT_NUMBER=`
  - `ARAMEX_USERNAME=`
  - `ARAMEX_PASSWORD=`
  - `ARAMEX_RATE_URL=` → URL de l’endpoint de tarification (fournie par Aramex ou votre proxy).

Si Aramex est activé et configuré et que le client choisit Aramex, le devis est demandé à cet endpoint.

---

## API REST exposée (devis DHL / livraison)

L’app expose un endpoint pour obtenir un devis en JSON (utilise l’API DHL si `carrier=DHL` et configuré).

**GET** `/api/shipping/quote`

| Paramètre   | Type   | Défaut | Description                          |
|------------|--------|--------|--------------------------------------|
| `weight`   | float  | 1      | Poids total en kg                    |
| `country`  | string | TN     | Code pays (ex. TN, FR)               |
| `carrier`  | string | DHL    | DHL, ARAMEX ou POSTE                 |
| `postal_code` | string | (vide) | Code postal destination (optionnel) |
| `city`     | string | (vide) | Ville (optionnel)                    |
| `address`  | string | (vide) | Adresse (optionnel)                  |

**Exemple** : `GET /api/shipping/quote?weight=2.5&country=TN&carrier=DHL`

**Réponse** (200) :
```json
{
  "carrier": "DHL",
  "cost": 12.50,
  "currency": "TND",
  "etaDays": 2,
  "productCode": "N"
}
```

Quand `carrier=DHL` et que le `.env` DHL est renseigné, le montant et le délai viennent de l’**API MyDHL (Rating)**. Sinon, formule interne ou Aramex selon la config.

---

## Fichiers concernés

- `src/Service/Shipping/DistanceService.php` – OpenRouteService (geocode + matrix).
- `src/Service/Shipping/DhlQuoteClient.php` – Appel MyDHL API Rating.
- `src/Service/Shipping/AramexQuoteClient.php` – Appel endpoint REST Aramex.
- `src/Service/Shipping/ShippingQuoteService.php` – Orchestration : DHL ou Aramex si configurés, sinon formule + distance si disponible.
- `src/Controller/Api/ShippingController.php` – Endpoint GET `/api/shipping/quote` (devis en JSON).
- `config/services.yaml` – Injection des variables d’environnement pour ces services.
- `.env` – Variables listées ci‑dessus.

## Suivi (tracking)

Le modèle `ShippingQuote` prévoit un champ `trackingNumber`. La création d’étiquettes et le suivi (DHL Shipment, Aramex Waybill) peuvent être ajoutés plus tard via les APIs Shipment respectives.
