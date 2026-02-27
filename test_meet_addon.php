<?php
require_once __DIR__ . '/vendor/autoload.php';

$credentialsPath = 'C:\Users\OrdiOne\Desktop\Maternia\config\google\service-account.json';
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR]);
    $service = new Google\Service\Calendar($client);

    echo "Testing type: addOn\n";
    $start = new DateTime('+2 hours');
    $end = (clone $start)->modify('+30 minutes');
    
    $solutionKey = new Google\Service\Calendar\ConferenceSolutionKey();
    $solutionKey->setType('addOn');
    
    $confReq = new Google\Service\Calendar\CreateConferenceRequest();
    $confReq->setRequestId('test-addon-' . time());
    $confReq->setConferenceSolutionKey($solutionKey);
    
    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);
    
    $event = new Google\Service\Calendar\Event([
        'summary' => 'Test Meet addOn',
        'start' => ['dateTime' => $start->format(DATE_RFC3339)],
        'end' => ['dateTime' => $end->format(DATE_RFC3339)],
        'conferenceData' => $confData
    ]);
    
    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    echo "SUCCESS! ID: " . $created->getId() . "\n";
    
    // Google needs a moment to create the conference
    for($i=0; $i<3; $i++) {
        sleep(2);
        $created = $service->events->get($calendarId, $created->getId());
        $conf = $created->getConferenceData();
        if ($conf) {
            echo "Conference Data Found!\n";
            if ($conf->getEntryPoints()) {
                foreach ($conf->getEntryPoints() as $ep) {
                    echo "EntryPoint: " . $ep->getUri() . " (" . $ep->getEntryPointType() . ")\n";
                }
            } else {
                echo "No EntryPoints yet...\n";
            }
            if ($conf->getCreateRequest() && $conf->getCreateRequest()->getStatus()) {
                echo "Status: " . $conf->getCreateRequest()->getStatus()->getStatusCode() . "\n";
            }
        } else {
            echo "No Conference Data yet...\n";
        }
    }
    
    $service->events->delete($calendarId, $created->getId());

} catch (\Exception $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
