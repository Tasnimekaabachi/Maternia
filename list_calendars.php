<?php
require_once __DIR__ . '/vendor/autoload.php';

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

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_READONLY]);
    $service = new Google\Service\Calendar($client);

    $calendarList = $service->calendarList->listCalendarList();
    
    echo "=== CALENDARS ACCESSIBLE BY SERVICE ACCOUNT ===\n";
    foreach ($calendarList->getItems() as $calendarListEntry) {
        echo "ID: " . $calendarListEntry->getId() . " | Summary: " . $calendarListEntry->getSummary() . "\n";
    }
    if (count($calendarList->getItems()) == 0) {
        echo "Aucun calendrier n'est partagé avec ce compte de service.\n";
    }

} catch (\Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
}
