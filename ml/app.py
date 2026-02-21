from flask import Flask, request, jsonify
import pickle

app = Flask(__name__)

with open('model.pkl', 'rb') as f:
    model = pickle.load(f)

with open('label_encoder.pkl', 'rb') as f:
    le = pickle.load(f)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.json

    features = [[
        int(data['nausee']),
        int(data['vomissement']),
        int(data['saignement']),
        int(data['fievre']),
        int(data['douleur_abdominale']),
        int(data['fatigue']),
        int(data['vertiges'])
    ]]

    prediction = model.predict(features)[0]
    risk = le.inverse_transform([prediction])[0]

    messages = {
        'Low': 'Grossesse à faible risque. Continuez les consultations régulières.',
        'Mid': 'Risque modéré détecté. Une consultation médicale est recommandée.',
        'High': 'Risque élevé détecté ! Consultez un médecin immédiatement.'
    }

    return jsonify({
        'risk': risk,
        'message': messages[risk]
    })

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok'})

if __name__ == '__main__':
    app.run(port=5001, debug=True)