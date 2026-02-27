<?php
require_once __DIR__ . '/vendor/autoload.php';

// On utilise les mêmes paramètres que le .env
$credentialsPath = 'C:\\Users\\OrdiOne\\Desktop\\Maternia\\config\\google\\service-account.json';
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

echo "=== TEST GÉNÉRATION LIEN MEET MATERNIA ===\n";

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_EVENTS]);
    $service = new Google\Service\Calendar($client);

    $start = new DateTime('+2 hours');
    $end = (clone $start)->modify('+30 minutes');

    $confReq = new Google\Service\Calendar\CreateConferenceRequest();
    $confReq->setRequestId('maternia-test-' . time());
    
    // PRÉCISION DE LA SOLUTION MEET
    $solutionKey = new Google\Service\Calendar\ConferenceSolutionKey();
    $solutionKey->setType('hangoutsMeet');
    $confReq->setConferenceSolutionKey($solutionKey);

    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary' => 'MATERNIA - TEST RÉEL MEET',
        'start' => ['dateTime' => $start->format(DATE_RFC3339)],
        'end' => ['dateTime' => $end->format(DATE_RFC3339)],
        'conferenceData' => $confData
    ]);

    // Insertion avec conferenceDataVersion = 1
    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    $eventId = $created->getId();
    
    echo "✅ Événement créé (ID: $eventId)\n";
    echo "Attente de génération du lien (max 10s)...\n";

    $meetLink = null;
    for ($i = 0; $i < 5; $i++) {
        sleep(2);
        $refreshed = $service->events->get($calendarId, $eventId);
        $meetLink = $refreshed->getHangoutLink();
        
        if ($meetLink) {
            echo "\n🎉 SUCCÈS ! Lien Google Meet généré :\n$meetLink\n\n";
            break;
        } else {
            echo "  Tentative " . ($i+1) . "... Pas encore.\n";
        }
    }

    // On supprime l'événement test
    $service->events->delete($calendarId, $eventId);
    echo "Nettoyage terminé.\n";

} catch (\Exception $e) {
    echo "\n❌ ERREUR : " . $e->getMessage() . "\n";
}
