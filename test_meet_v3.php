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
$calendarId = getEnvVar('GOOGLE_MEET_CALENDAR_ID');

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
    $confReq->setRequestId('test' . time());
    $confReq->setConferenceSolutionKey($solutionKey);
    
    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary' => 'Test Meet Maternia V3',
        'start' => ['dateTime' => $start->format(DATE_RFC3339)],
        'end' => ['dateTime' => $end->format(DATE_RFC3339)],
        'conferenceData' => $confData
    ]);

    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    
    sleep(2);
    $created = $service->events->get($calendarId, $created->getId());

    $link = $created->getHangoutLink();
    echo "LINK_RESULT:" . ($link ?: 'NONE') . "\n";
    
    if ($created->getConferenceData()) {
        echo "CONF_STATUS:" . $created->getConferenceData()->getCreateRequest()->getStatus()->getStatusCode() . "\n";
    } else {
        echo "NO_CONF_DATA_IN_RESPONSE\n";
    }

    $service->events->delete($calendarId, $created->getId());

} catch (\Exception $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
