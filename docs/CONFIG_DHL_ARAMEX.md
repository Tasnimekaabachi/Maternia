# Configurer DHL et Aramex dans Maternia

Les variables sont dans le fichier **`.env`** à la racine du projet. Une fois remplies, vide le cache : `php bin/console cache:clear`.

---

## DHL (MyDHL API)

Sur le portail DHL (**Reference Docs**) :

1. **Servers** (menu déroulant) : choisis l’environnement :
   - **Mock** : `https://api-mock.dhl.com/mydhlapi` — données de test, pas de compte DHL.
   - **Test** : `https://express.api.dhl.com/mydhlapi/test` — environnement de test (accès demandé via le lien « here » de la page).
2. **Authorize** (bouton rouge) ouvre **« Available authorizations »** :
   - **basicAuth (http, Basic)** : obligatoire. Renseigne **Username** et **Password** (pour le mock : `demo-key` + un mot de passe de ton choix), puis **Set Key**.
   - **apiKey (apiKey)** : optionnel. Si demandé, « Add your app key » = la clé à envoyer dans le header **X-API-KEY** ; tu peux la recopier dans `.env` en `DHL_API_KEY=...`.
3. **Rating** : `GET /rates` (un colis) et `POST /rates` (plusieurs colis) pour obtenir tarif + délai.

### 1. Tester avec le Mock Server (sans compte DHL)

Dans la fenêtre **« Available authorizations »** du portail DHL, l’auth **basicAuth** utilise :
- **Username :** `demo-key`
- **Password :** la valeur que tu as saisie (celle masquée en ******)

À mettre dans ton **`.env`** :

1. URL du mock :
   ```env
   DHL_API_BASE_URL=https://api-mock.dhl.com/mydhlapi
   ```
2. Même identifiants que dans le portail (Basic Auth) :
   ```env
   DHL_ACCOUNT_NUMBER=123456789
   DHL_USERNAME=demo-key
   DHL_PASSWORD=ta_valeur_password_du_portail
   ```
   Remplace `ta_valeur_password_du_portail` par le mot de passe que tu as entré dans **Authorize** (celui affiché en ******). Pour le mock, un numéro de compte factice comme `123456789` suffit souvent.

3. **apiKey (X-API-KEY)** : si le portail affiche une section « apiKey » avec « Add your app key » et « In: header », tu peux soit l’ignorer (le mock marche souvent avec le seul Basic Auth), soit saisir la clé dans le portail et la recopier dans `.env` :
   ```env
   DHL_API_KEY=ta_cle_ici
   ```
   L’app enverra alors le header `X-API-KEY` en plus du Basic Auth.

4. Lance un checkout en choisissant **DHL** : les frais affichés viendront du mock (données de test).

### 2. Obtenir l’accès réel (test / prod)

- Tu dois avoir un **compte client DHL Express** (entreprise).
- Va sur **https://developer.dhl.com** → suis le processus décrit sur le portail pour demander l’accès à la MyDHL API.
- Utilise les **endpoints / environnements** listés sur le portail (comme indiqué dans la page d’accueil).
- DHL te fournit : **Account Number**, **Username**, **Password** (pour l’env test et/ou prod).

### 3. Remplir le `.env` (test ou prod)

Une fois les identifiants reçus :

```env
# Test (compte DHL requis)
DHL_API_BASE_URL=https://express.api.dhl.com/mydhlapi/test

# En PRODUCTION :
# DHL_API_BASE_URL=https://express.api.dhl.com/mydhlapi

DHL_ACCOUNT_NUMBER=123456789
DHL_USERNAME=ton_username_api
DHL_PASSWORD=ton_password_api
```

- **Ne commite pas** le `.env` avec les vrais mots de passe : utilise `.env.local` pour les secrets (il est ignoré par git) ou des secrets Symfony.

### 3. Vérifier

- Va sur le panier, ajoute un produit, choisis **DHL** comme transporteur, remplis une adresse et valide.
- Si la config est bonne, les frais de livraison affichés viennent de l’API DHL. Sinon, l’app utilise la formule interne (et tu peux regarder les logs pour les erreurs API).

---

## Aramex (Rate API)

### 1. Obtenir l’accès

- Aramex propose des APIs pour les professionnels (Rate Calculator, création d’envois, suivi).
- Contacte **Aramex** (commercial ou support développeurs) pour :
  - Un **compte / numéro de compte** Aramex
  - Un accès à l’**API de tarification** (Rate Calculator ou équivalent REST)
  - Les identifiants : **username**, **password**
  - L’**URL de l’endpoint** pour obtenir un tarif (ex. `https://.../RateCalculator` ou URL fournie par Aramex)

Leur doc développeurs : **https://dr.aramex.com** (selon ta région). L’API officielle est souvent en **XML** ; si Aramex ne te donne qu’un service XML, il faudra soit un petit proxy qui renvoie du JSON, soit adapter notre client pour parler XML.

### 2. Remplir le `.env`

Dans `.env`, active Aramex et mets les valeurs fournies par Aramex :

```env
ARAMEX_ENABLED=1
ARAMEX_ACCOUNT_NUMBER=ton_numero_compte
ARAMEX_USERNAME=ton_username
ARAMEX_PASSWORD=ton_password
ARAMEX_RATE_URL=https://url-fournie-par-aramex/rate
```

- **ARAMEX_RATE_URL** : c’est l’URL exacte que Aramex (ou ton intégrateur) te donne pour interroger les tarifs. Si tu n’as qu’une URL en SOAP/XML, notre client actuel attend du REST JSON ; il faudra soit une URL qui renvoie du JSON, soit adapter le code.

### 3. Format de réponse attendu (si ton endpoint est REST/JSON)

L’application s’attend à une réponse JSON contenant au moins :

- Un **montant** : champ `amount` ou `totalCharge` (nombre)
- Optionnel : **délai en jours** : champ `days` ou `deliveryDays` (entier)

Exemple :

```json
{
  "amount": 15.50,
  "currency": "TND",
  "days": 2
}
```

Si ton API Aramex renvoie d’autres noms de champs, on peut adapter le client PHP (fichier `AramexQuoteClient`) pour les lire.

### 4. Vérifier

- Panier → transporteur **Aramex** → valider la commande.
- Si tout est bon, les frais viennent d’Aramex. Sinon, vérifier l’URL, les identifiants et les logs.

---

## Récapitulatif des variables `.env` (DHL + Aramex)

| Variable | Description |
|----------|-------------|
| `DHL_API_BASE_URL` | `https://express.api.dhl.com/mydhlapi/test` (test) ou `.../mydhlapi` (prod) |
| `DHL_ACCOUNT_NUMBER` | Numéro de compte DHL Express |
| `DHL_USERNAME` | Username fourni par DHL pour l’API |
| `DHL_PASSWORD` | Password fourni par DHL pour l’API |
| `ARAMEX_ENABLED` | `1` pour activer Aramex |
| `ARAMEX_ACCOUNT_NUMBER` | Numéro de compte Aramex |
| `ARAMEX_USERNAME` | Username API Aramex |
| `ARAMEX_PASSWORD` | Password API Aramex |
| `ARAMEX_RATE_URL` | URL de l’endpoint de tarification Aramex |

Après toute modification du `.env`, exécuter : **`php bin/console cache:clear`**.
