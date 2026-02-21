import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import classification_report
import pickle

# Charger le dataset
df = pd.read_csv('dataset_grossesse_symptomes_1000.csv')

# Features et target
X = df[['nausee', 'vomissement', 'saignement', 'fievre',
        'douleur_abdominale', 'fatigue', 'vertiges']]
y = df['RiskLevel']

# Encoder Low/Mid/High en chiffres
le = LabelEncoder()
y_encoded = le.fit_transform(y)

print("Classes:", le.classes_)

# Split train/test
X_train, X_test, y_train, y_test = train_test_split(
    X, y_encoded, test_size=0.2, random_state=42
)

# Entraîner le modèle
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Évaluer
y_pred = model.predict(X_test)
print("\nAccuracy:", model.score(X_test, y_test))
print("\nRapport détaillé:")
print(classification_report(y_test, y_pred, target_names=le.classes_))

# Sauvegarder
with open('model.pkl', 'wb') as f:
    pickle.dump(model, f)

with open('label_encoder.pkl', 'wb') as f:
    pickle.dump(le, f)

print("\n✅ Modèle sauvegardé !")