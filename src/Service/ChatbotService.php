<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    ) {}

    public function ask(string $question, array $mamanContext = []): string
    {
        // Contexte médical personnalisé
        $context = "Tu es un assistant médical spécialisé en grossesse pour l'application Maternia. 
        Réponds toujours en français, de manière bienveillante et professionnelle.
        Ne remplace jamais un médecin — rappelle-le si nécessaire.";

        if (!empty($mamanContext)) {
            $context .= "\n\nInformations sur la maman :";
            if (isset($mamanContext['trimestre'])) $context .= "\n- Trimestre : " . $mamanContext['trimestre'];
            if (isset($mamanContext['semaine'])) $context .= "\n- Semaine : " . $mamanContext['semaine'];
            if (isset($mamanContext['groupeSanguin'])) $context .= "\n- Groupe sanguin : " . $mamanContext['groupeSanguin'];
            if (isset($mamanContext['maladies'])) $context .= "\n- Maladies chroniques : " . $mamanContext['maladies'];
            if (isset($mamanContext['allergies'])) $context .= "\n- Allergies : " . $mamanContext['allergies'];
        }

        try {
            $response = $this->client->request('POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $this->apiKey,
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $context . "\n\nQuestion : " . $question]
                                ]
                            ]
                        ]
                    ]
                ]
            );

            $data = $response->toArray();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Désolée, je n\'ai pas pu répondre.';

} catch (\Exception $e) {
    if (str_contains($e->getMessage(), '429')) {
        return 'Je suis un peu occupée en ce moment 💕 Veuillez réessayer dans quelques secondes.';
    }
    return 'Service temporairement indisponible. Veuillez réessayer.';
}
}
}