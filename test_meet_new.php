<?php
require_once __DIR__ . '/vendor/autoload.php';

// Plus robuste que parse_ini_file pour un .env
function getEnvVar($key) {
    $lines = file(__DIR__ . '/.env.local', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, $key . '=') === 0) {
            return substr($line, strlen($key) + 1);
        }
    }
    return '';
}

$credentialsPath = getEnvVar('GOOGLE_MEET_CREDENTIALS_PATH');
$calendarId = getEnvVar('GOOGLE_MEET_CALENDAR_ID');

echo "=== TEST GOOGLE MEET (NOUVEAU COMPTE) ===\n";
echo "Calendar ID: $calendarId\n\n";

if (empty($calendarId)) {
    echo "❌ ERREUR : GOOGLE_MEET_CALENDAR_ID non trouvé dans .env.local\n";
    exit;
}

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_EVENTS]);
    $service = new Google\Service\Calendar($client);

    $start = new DateTime('+1 day');
    $end = (clone $start)->modify('+30 minutes');

    $solutionKey = new Google\Service\Calendar\ConferenceSolutionKey();
    $solutionKey->setType('hangoutsMeet');
    $confReq = new Google\Service\Calendar\CreateConferenceRequest();
    $confReq->setRequestId('test-' . uniqid());
    $confReq->setConferenceSolutionKey($solutionKey);
    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary' => 'Test Maternia - Nouveau Compte',
        'start' => new Google\Service\Calendar\EventDateTime(['dateTime' => $start->format(DATE_RFC3339)]),
        'end' => new Google\Service\Calendar\EventDateTime(['dateTime' => $end->format(DATE_RFC3339)]),
    ]);
    $event->setConferenceData($confData);

    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

    echo "✅ SUCCÈS !\n";
    echo "Lien Meet généré : " . $created->getHangoutLink() . "\n";
    
    // Nettoyage
    $service->events->delete($calendarId, $created->getId());
    echo "Événement de test supprimé.\n";

} catch (\Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
    echo "\nCONSEIL : Vérifiez que vous avez bien ajouté l'email suivant dans les paramètres de PARTAGE du calendrier :\n";
    echo "maternia-calendar-service@fresh-heuristic-488501-e2.iam.gserviceaccount.com\n";
}
