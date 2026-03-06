from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import numpy as np

app = FastAPI(title="Maternia Cry Classifier", version="0.1.0")


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/predict-cry")
async def predict_cry(request: Request):
    """
    Reçoit de l'audio brut PCM int16 little-endian (mono).
    Retourne { label, confidence }.

    Remplace le stub par ton vrai modèle (Torch/TF/ONNX…).
    """
    raw = await request.body()
    if not raw:
        return JSONResponse({"error": "no audio"}, status_code=400)

    samples = np.frombuffer(raw, dtype="<i2").astype(np.float32) / 32768.0
    if samples.size == 0:
        return JSONResponse({"error": "empty audio"}, status_code=400)

    # --- LOGIQUE HEURISTIQUE SIMPLE (à remplacer par un vrai modèle IA) ---
    # On utilise l'énergie RMS comme indicateur d'intensité du pleur.
    energy = float(np.sqrt(np.mean(samples ** 2)))

    # Seuils très simplifiés uniquement pour la démo.
    # Dans la réalité, il faut entraîner un modèle dédié.
    if energy < 0.02:
        label = "calme"
        confidence = 0.6
    elif energy < 0.08:
        label = "inconfort léger"
        confidence = 0.7
    elif energy < 0.18:
        # Pleurs modérés et réguliers -> faim (hypothèse simplifiée)
        label = "faim"
        confidence = 0.8
    else:
        # Pleurs intenses et soutenus -> gaz / coliques (hypothèse simplifiée)
        label = "gaz ou coliques"
        confidence = 0.85
    # ----------------------------------------------------------------------

    return {
        "label": label,
        "confidence": confidence,
        "energy": energy,
    }

