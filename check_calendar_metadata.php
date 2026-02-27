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
    
    // There is no direct "list solutions" but we can check the metadata of an event that WAS created manually (if we had one)
    // Or check the calendar's "conferenceProperties"
    
    // Let's check another way: use 'null' for SolutionKey and see if it works
    
    echo "Done.\n";

} catch (\Exception $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
