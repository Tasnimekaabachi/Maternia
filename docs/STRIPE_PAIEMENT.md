# Intégration Stripe - Paiement par carte

## Configuration

1. **Créer un compte Stripe** : https://dashboard.stripe.com

2. **Récupérer les clés** (Tableau de bord → Développeurs → Clés API) :
   - Clé secrète (sk_test_xxx ou sk_live_xxx)
   - Clé publique (pk_test_xxx ou pk_live_xxx)

3. **Configurer le fichier `.env`** :
   ```
   STRIPE_SECRET_KEY=sk_test_xxx
   STRIPE_PUBLISHABLE_KEY=pk_test_xxx
   STRIPE_WEBHOOK_SECRET=whsec_xxx
   STRIPE_CURRENCY=eur
   STRIPE_TND_TO_EUR=0.29
   ```

4. **Webhook Stripe** (pour confirmer les paiements) :
   - Dashboard Stripe → Développeurs → Webhooks → Ajouter un endpoint
   - URL : `https://votre-domaine.com/api/payment/webhook`
   - Événement à écouter : `payment_intent.succeeded`
   - Copier le secret du webhook dans `STRIPE_WEBHOOK_SECRET`

5. **En local** : utiliser le script fourni (met à jour `.env` automatiquement) :
   ```powershell
   .\scripts\stripe-listen.ps1
   ```
   Ou lancer Symfony + Stripe en une commande :
   ```powershell
   .\scripts\start-dev.ps1
   ```
   *(Installer Stripe CLI : `winget install -e --id Stripe.StripeCli`)*
   *(Si "script désactivé" : `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`)*

## Endpoints API

| Méthode | URL | Description |
|---------|-----|-------------|
| POST | `/api/payment/create-intent/{id}` | Crée une intention de paiement pour la commande |
| POST | `/api/payment/webhook` | Reçoit les webhooks Stripe (signature vérifiée) |

## Flux

1. L'utilisateur valide le panier en choisissant "Paiement par carte"
2. Redirection vers `/panier/payment/{id}`
3. La page appelle `POST /api/payment/create-intent/{id}` pour obtenir le `clientSecret`
4. Stripe.js affiche le formulaire de paiement
5. Après paiement réussi, Stripe redirige vers la page de succès
6. Le webhook `payment_intent.succeeded` met à jour la commande et envoie l'email de confirmation

## TND → EUR

Stripe ne supporte pas le dinar tunisien. Le montant est converti en EUR via `STRIPE_TND_TO_EUR` (ex: 0.29).
