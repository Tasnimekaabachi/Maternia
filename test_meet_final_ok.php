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
    
    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary' => 'Test Meet Maternia Final',
        'start' => ['dateTime' => $start->format(DATE_RFC3339)],
        'end' => ['dateTime' => $end->format(DATE_RFC3339)],
        'conferenceData' => $confData
    ]);

    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    
    sleep(2);
    $created = $service->events->get($calendarId, $created->getId());

    $link = $created->getHangoutLink();
    echo "MEET_LINK:" . ($link ?: 'NONE') . "\n";
    
    $service->events->delete($calendarId, $created->getId());

} catch (\Exception $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
