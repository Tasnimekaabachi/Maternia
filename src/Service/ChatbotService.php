<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function ask(string $question, array $mamanContext = []): string
    {
        // Construction du profil médical intelligent
        $profil = "";

        if (!empty($mamanContext['groupeSanguin'])) {
            $profil .= "Groupe sanguin : " . $mamanContext['groupeSanguin'] . ". ";
        }
        if (!empty($mamanContext['maladies'])) {
            $profil .= "Maladies chroniques : " . $mamanContext['maladies'] . ". ";
        }
        if (!empty($mamanContext['allergies'])) {
            $profil .= "Allergies : " . $mamanContext['allergies'] . ". ";
        }
        if (!empty($mamanContext['semaine'])) {
            $profil .= "Semaine de grossesse : " . $mamanContext['semaine'] . ". ";
        }
        if (!empty($mamanContext['trimestre'])) {
            $profil .= "Trimestre : " . $mamanContext['trimestre'] . ". ";
        }

        // PROMPT ULTRA HUMAIN (spécial Maternia)
        $prompt = "
Tu es Maternia AI 🤱, une assistante médicale virtuelle spécialisée en suivi de grossesse.

PERSONNALITÉ :
- Douce, rassurante et humaine
- Comme une sage-femme bienveillante
- Empathique et chaleureuse
- Professionnelle mais simple à comprendre

RÈGLES DE RÉPONSE :
- Réponds en français naturel
- 2 à 3 phrases maximum
- Pas de listes longues
- Pas de ton robotique
- Adapte les conseils selon le trimestre de grossesse
- Reste rassurante (jamais alarmiste)
- Rappelle doucement que tu ne remplaces pas un médecin si nécessaire
- Si la question évoque douleur intense, saignement, fièvre élevée ou urgence,
  conseille immédiatement de consulter un professionnel de santé.

CONTEXTE MÉDICAL DE LA MAMAN :
$profil

QUESTION DE LA MAMAN :
$question
";

        try {
            $response = $this->client->request(
                'POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'timeout' => 30,
                    'json' => [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.9, // + humain
                            'maxOutputTokens' => 1028, // idéal pour 2-3 phrases
                            'topP' => 0.95
                        ]
                    ]
                ]
            );

            $data = $response->toArray(false);

            // Vérification réponse API
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return "Je suis là pour vous aider 💕 Pouvez-vous reformuler votre question ?";
            }

            $text = $data['candidates'][0]['content']['parts'][0]['text'];

            // Nettoyage affichage (chat UI)
            $text = trim($text);
            $text = str_replace(["\n\n", "\n"], " ", $text);

            return $text;

        } catch (\Exception $e) {
            // Réponse humaine même en cas d’erreur API (SUPER important pour la démo)
            return "Je rencontre un petit souci technique 💕 mais je suis toujours là pour vous accompagner. Réessayez dans quelques secondes.";
        }
    }
}