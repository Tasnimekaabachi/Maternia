# Maternia

Application dédiée aux futures et jeunes mamans : suivi de grossesse, consultations, marketplace, événements, babysitting.

---

## Tout est prêt – Analyse pleur bébé

### 1. Config (optionnel)

**SMS** – Dans **`.env.local`** remplace `YOUR_ACCOUNT_SID`, `YOUR_AUTH_TOKEN` et le numéro Twilio pour que les SMS partent à la babysitter.

**Emails** – Pour envoyer les emails (babysitter + parent « récupérez votre bébé »), ajoute dans `.env.local` par ex. :  
`MAILER_DSN=smtp://user:pass@smtp.example.com:587`

Redémarre Symfony après modification.

### 2. Lancer les services

**Terminal 1 – Service ML (analyse des pleurs)**  
À la racine du projet :

```powershell
.\scripts\start_cry_service.ps1
```

**Terminal 2 – Symfony**

```powershell
symfony serve
```

### 3. Utiliser la page

1. Ouvre le back office (ex. `http://127.0.0.1:8000/admin`).
2. Menu → **Analyse pleur bébé**.
3. Choisis une **babysitter**, le **nom du bébé**, et (pour l’email « récupérez votre bébé ») le **nom** et l’**email du parent**.
4. Clique sur **Démarrer la capture** → autorise le micro.
5. En cas de pleurs détectés : alerte créée, SMS à la babysitter (si Twilio configuré), email à la babysitter (si son email est renseigné sur l’offre), email au parent « Veuillez récupérer votre bébé » (si email parent saisi).

---

Documentation détaillée et dépannage : **ml/cry_service/README.md**.
