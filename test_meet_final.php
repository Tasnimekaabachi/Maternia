<?php
require_once __DIR__ . '/vendor/autoload.php';

function getEnvVar($key) {
    if (!file_exists(__DIR__ . '/.env.local')) return '';
    $lines = file(__DIR__ . '/.env.local', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, $key . '=') === 0) {
            return trim(substr($line, strlen($key) + 1));
        }
    }
    return '';
}

$credentialsPath = getEnvVar('GOOGLE_MEET_CREDENTIALS_PATH');
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

echo "TEST CONFIG:\n";
echo "Credentials: $credentialsPath\n";
echo "Calendar ID: $calendarId\n\n";

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_EVENTS]);
    $service = new Google\Service\Calendar($client);

    $start = new DateTime('+2 hours');
    $end = (clone $start)->modify('+30 minutes');

    $confReq = new Google\Service\Calendar\CreateConferenceRequest();
    $confReq->setRequestId('test' . time());
    
    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary' => 'Test Final Maternia',
        'start' => ['dateTime' => $start->format(DATE_RFC3339)],
        'end' => ['dateTime' => $end->format(DATE_RFC3339)],
        'conferenceData' => $confData
    ]);

    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

    echo "RESULTAT:\n";
    echo "ID Event: " . $created->getId() . "\n";
    echo "Meet Link: " . ($created->getHangoutLink() ?: 'NULL') . "\n";
    
    $conf = $created->getConferenceData();
    if ($conf && $conf->getEntryPoints()) {
        foreach ($conf->getEntryPoints() as $ep) {
            echo "EntryPoint: " . $ep->getUri() . " (" . $ep->getEntryPointType() . ")\n";
        }
    }

    $service->events->delete($calendarId, $created->getId());
    echo "\nTest fini, evenement supprime.\n";

} catch (\Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
