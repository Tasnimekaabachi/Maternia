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
    $client->setScopes([Google\Service\Calendar::CALENDAR_READONLY]);
    $service = new Google\Service\Calendar($client);

    $calendar = $service->calendars->get($calendarId);
    echo "Calendar Summary: " . $calendar->getSummary() . "\n";
    
    // On ne peut pas facilement lister les solutions via API sans metadata d'agenda
    // Mais on peut tester la création sans type pour voir ce que Google suggère
    
} catch (\Exception $e) {
    echo "ERROR:" . $e->getMessage() . "\n";
}
