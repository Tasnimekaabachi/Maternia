# Analyse pleur bébé – Tout en un

Ce dossier contient le **service Python** qui analyse l’audio des pleurs. Il doit tourner en même temps que Symfony pour que la page **Backoffice → Analyse pleur bébé** fonctionne (classification + alerte + SMS).

---

## 1. Prérequis

- **Python 3.10+** (commande `py` disponible sur Windows)
- **Symfony** (projet Maternia) lancé
- **Optionnel pour les SMS** : compte Twilio + `TWILIO_DSN` dans `.env.local`

---

## 2. Installation (une seule fois)

Dans un terminal, à la racine de ce dossier (`Maternia\ml\cry_service\`) :

```powershell
py -m venv .venv
.\.venv\Scripts\Activate.ps1
py -m pip install -U pip
pip install -r requirements.txt
```

---

## 3. Démarrer le service ML

**Option A – À la main**

```powershell
cd ml\cry_service
.\.venv\Scripts\Activate.ps1
uvicorn main:app --host 127.0.0.1 --port 8001 --reload
```

**Option B – Script à la racine du projet**

À la racine de Maternia :

```powershell
.\scripts\start_cry_service.ps1
```

Laisse ce terminal ouvert. Vérification : ouvrir **http://127.0.0.1:8001/health** → doit afficher `{"status":"ok"}`.

---

## 4. Démarrer Symfony

Dans un **autre** terminal, à la racine du projet Maternia :

```powershell
symfony serve
```

(ou `php -S 127.0.0.1:8000 -t public` selon ta config.)

---

## 5. Utiliser la page « Analyse pleur bébé »

1. Aller sur le back office (ex. **http://127.0.0.1:8000/admin**).
2. Menu de gauche → **Analyse pleur bébé**.
3. Choisir une **babysitter** dans la liste.
4. (Optionnel) Saisir le **nom du bébé**.
5. Cliquer sur **Démarrer la capture** → autoriser le micro.
6. Le **spectre** s’affiche, la **classification** se met à jour (calme / faim / gaz ou coliques, etc.).
7. Si le cri n’est pas « calme », une **alerte** est créée et un **SMS** est envoyé à la babysitter (si Twilio est configuré).

---

## 6. Configurer les SMS (Twilio)

Pour que les alertes envoient un SMS au numéro de la babysitter :

1. Créer un fichier **`.env.local`** à la racine du projet (à côté de `.env`).
2. Y ajouter (avec tes identifiants Twilio) :

   ```env
   TWILIO_DSN=twilio://ACxxxxxxxx:VOTRE_AUTH_TOKEN@default?from=+216XXXXXXXX
   ```

   - **ACxxxxxxxx** = Account SID (dashboard Twilio)
   - **VOTRE_AUTH_TOKEN** = Auth Token (dashboard Twilio)
   - **from=+216XXXXXXXX** = numéro d’envoi Twilio au format E.164 (ex. Tunisie +216…)

3. Redémarrer le serveur Symfony.

Les numéros des offres babysitter sont convertis automatiquement en format E.164 (ex. `71 234 567` → `+21671234567`).

---

## 7. API du service Python

- **GET** `/health` → `{"status":"ok"}`
- **POST** `/predict-cry`  
  - Corps : audio brut **PCM 16 bits little-endian mono**.  
  - Réponse : `{"label": "calme"|"faim"|"gaz ou coliques"|..., "confidence": 0.xx, "energy": 0.xx}`

Symfony appelle cette URL depuis `CryAnalysisController` : `POST http://127.0.0.1:8001/predict-cry`.

---

## 8. Dépannage

| Problème | Solution |
|----------|----------|
| « Erreur API » sur la page | Le service Python n’est pas démarré ou pas joignable. Lancer `uvicorn` (étape 3) et vérifier **http://127.0.0.1:8001/health**. |
| Pas de SMS reçu | Vérifier `TWILIO_DSN` dans `.env.local`, le numéro de l’offre babysitter en base, et les logs Symfony (`var/log/dev.log` : « Envoi SMS alerte pleurs échoué »). |
| Le bouton « Démarrer la capture » ne fait rien | Vérifier que le JavaScript du back office est chargé (`importmap('app')` dans `templates/admin/base.html.twig`) et qu’une babysitter est sélectionnée. |
