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

    /**
     * @param array<mixed> $mamanContext
     * @param array<mixed> $conversationHistory
     */
    public function ask(string $question, array $mamanContext = [], array $conversationHistory = []): string
    {
        // ⚠️ DÉTECTION URGENCE MÉDICALE
        $urgenceKeywords = [
            'saignement', 'saigne', 'hémorragie',
            'douleur intense', 'douleur forte', 'très mal',
            'fièvre élevée', 'fièvre forte', '39', '40 degrés',
            'je tombe', 'je perds connaissance', 'vertige fort',
            'bébé ne bouge plus', 'bébé bouge pas',
            'contractions', 'perte de liquide', 'poche des eaux',
            'urgence', 'je saigne', 'sang',
        ];

        $questionLower = mb_strtolower($question);
        foreach ($urgenceKeywords as $keyword) {
            if (str_contains($questionLower, $keyword)) {
                return "🚨 Ce que vous décrivez nécessite une attention médicale immédiate. Appelez le 15 (SAMU) ou rendez-vous aux urgences sans attendre. Votre santé et celle de votre bébé passent avant tout 💕";
            }
        }

        // ── Construction du profil médical ────────────────────────
        $profil = '';

        // ✅ is_string() garantit le type → pas de mixed
        $groupeSanguin = $mamanContext['groupeSanguin'] ?? null;
        $maladies      = $mamanContext['maladies']      ?? null;
        $allergies     = $mamanContext['allergies']     ?? null;
        $semaine       = $mamanContext['semaine']       ?? null;
        $trimestre     = $mamanContext['trimestre']     ?? null;

        if (is_string($groupeSanguin) && $groupeSanguin !== '') {
            $profil .= 'Groupe sanguin : ' . $groupeSanguin . '. ';
        }
        if (is_string($maladies) && $maladies !== '') {
            $profil .= 'Maladies chroniques : ' . $maladies . '. ';
        }
        if (is_string($allergies) && $allergies !== '') {
            $profil .= 'Allergies : ' . $allergies . '. ';
        }
        if ($semaine !== null) {
            $profil .= 'Semaine de grossesse : ' . (string) $semaine . '. ';
        }
        if ($trimestre !== null) {
            $profil .= 'Trimestre : ' . (string) $trimestre . '. ';
        }

        // ── Prompt système ────────────────────────────────────────
        $systemPrompt = "
Tu es Maternia AI 🤱, une assistante médicale virtuelle spécialisée en suivi de grossesse.

PERSONNALITÉ :
- Douce, rassurante et très humaine
- Comme une sage-femme bienveillante et chaleureuse
- Utilise des emojis doux (💕 🤱 🌸 ✨ 👶 🌿) naturellement dans tes réponses
- Appelle toujours la maman \"chère future maman\" ou \"ma belle\" ou \"chère maman\"

RÈGLES DE RÉPONSE :
- Réponds en français naturel et chaleureux
- 2 à 3 phrases maximum
- Pas de listes longues
- Pas de ton robotique
- Adapte les conseils selon le trimestre de grossesse
- Reste toujours rassurante et positive
- Rappelle doucement que tu ne remplaces pas un médecin si nécessaire
- Termine parfois par une phrase d'encouragement

CONTEXTE MÉDICAL DE LA MAMAN :
$profil
";

        // ── Construction des messages avec historique ─────────────
        $messages = [];

        foreach ($conversationHistory as $entry) {
            // ✅ Vérification explicite que $entry est un array
            if (!is_array($entry)) {
                continue;
            }

            $role    = $entry['role']    ?? null;
            $content = $entry['content'] ?? null;

            // ✅ is_string() au lieu de cast → niveau 9
            if (!is_string($role) || !is_string($content) || $role === '' || $content === '') {
                continue;
            }

            $messages[] = [
                'role'  => $role,
                'parts' => [['text' => $content]],
            ];
        }

        $messages[] = [
            'role'  => 'user',
            'parts' => [['text' => $question]],
        ];

        // ── Appel API Gemini ──────────────────────────────────────
        try {
            $response = $this->client->request(
                'POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey,
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'timeout' => 30,
                    'json'    => [
                        'system_instruction' => [
                            'parts' => [['text' => $systemPrompt]],
                        ],
                        'contents'         => $messages,
                        'generationConfig' => [
                            'temperature'     => 0.9,
                            'maxOutputTokens' => 1024,
                            'topP'            => 0.95,
                        ],
                        'safetySettings' => [
                            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                            ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_ONLY_HIGH'],
                            ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_ONLY_HIGH'],
                            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ],
                    ],
                ]
            );

            $data = $response->toArray(false);

            // ✅ is_string() sur la réponse API → niveau 9
            $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!is_string($rawText) || $rawText === '') {
                return "Je suis là pour vous aider 💕 Pouvez-vous reformuler votre question ?";
            }

            $text = trim($rawText);
            $text = str_replace(["\n\n", "\n"], ' ', $text);

            return $text;

        } catch (\Exception $e) {
            return "Je rencontre un petit souci technique 💕 mais je suis toujours là pour vous accompagner. Réessayez dans quelques secondes.";
        }
    }
}