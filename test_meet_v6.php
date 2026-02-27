<?php
require_once __DIR__ . '/vendor/autoload.php';

$credentialsPath = 'C:\Users\OrdiOne\Desktop\Maternia\config\google\service-account.json';
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_EVENTS]);
    $service = new Google\Service\Calendar($client);

    $start = new DateTime('+1 day');
    $end = (clone $start)->modify('+30 minutes');

    $confReq = new Google\Service\Calendar\CreateConferenceRequest();
    $confReq->setRequestId('test' . time());
    
    // Ajout explicite de la solution key
    $solutionKey = new Google\Service\Calendar\ConferenceSolutionKey();
    $solutionKey->setType('hangoutsMeet');
    $confReq->setConferenceSolutionKey($solutionKey);

    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary' => 'Test Meet Maternia Final V6',
        'start' => ['dateTime' => $start->format(DATE_RFC3339)],
        'end' => ['dateTime' => $end->format(DATE_RFC3339)],
        'conferenceData' => $confData
    ]);

    // ESSAI AVEC CONFERENCE DATA VERSION 1
    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    
    echo "EVENT_ID:" . $created->getId() . "\n";
    
    for ($i = 0; $i < 5; $i++) {
        sleep(2);
        $created = $service->events->get($calendarId, $created->getId());
        $link = $created->getHangoutLink();
        if ($link) {
            echo "MEET_LINK_FOUND:" . $link . "\n";
            break;
        } else {
            echo "Attempt " . ($i+1) . ": No link yet...\n";
            $cd = $created->getConferenceData();
            if ($cd) {
                echo "Has Conference Data\n";
            } else {
                echo "No Conference Data object\n";
            }
        }
    }
    
    $service->events->delete($calendarId, $created->getId());

} catch (\Exception $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
