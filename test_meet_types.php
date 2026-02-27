<?php
require_once __DIR__ . '/vendor/autoload.php';

$credentialsPath = 'C:\Users\OrdiOne\Desktop\Maternia\config\google\service-account.json';
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR]);
    $service = new Google\Service\Calendar($client);

    $calendar = $service->calendars->get($calendarId);
    echo "Calendar Summary: " . $calendar->getSummary() . "\n";
    
    // Check if we can get settings or something to see supported conference types
    // Actually, let's just try to create an event and see what the "allowed" types are in the error if possible
    // Or try 'addOn' which is sometimes used now
    
    $types = ['hangoutsMeet', 'addOn'];
    
    foreach ($types as $type) {
        echo "\nTesting type: $type\n";
        try {
            $start = new DateTime('+2 hours');
            $end = (clone $start)->modify('+30 minutes');
            
            $solutionKey = new Google\Service\Calendar\ConferenceSolutionKey();
            $solutionKey->setType($type);
            
            $confReq = new Google\Service\Calendar\CreateConferenceRequest();
            $confReq->setRequestId('test-' . $type . '-' . time());
            $confReq->setConferenceSolutionKey($solutionKey);
            
            $confData = new Google\Service\Calendar\ConferenceData();
            $confData->setCreateRequest($confReq);
            
            $event = new Google\Service\Calendar\Event([
                'summary' => 'Test Meet ' . $type,
                'start' => ['dateTime' => $start->format(DATE_RFC3339)],
                'end' => ['dateTime' => $end->format(DATE_RFC3339)],
                'conferenceData' => $confData
            ]);
            
            $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
            echo "SUCCESS with $type! Event ID: " . $created->getId() . "\n";
            echo "Meet Link: " . ($created->getHangoutLink() ?: 'NONE') . "\n";
            $service->events->delete($calendarId, $created->getId());
        } catch (Exception $e) {
            echo "FAILURE with $type: " . $e->getMessage() . "\n";
        }
    }

} catch (\Exception $e) {
    echo "GLOBAL ERROR:" . $e->getMessage() . "\n";
}
