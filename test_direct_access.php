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

echo "Testing access to $calendarId\n";

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR]);
    $service = new Google\Service\Calendar($client);

    $calendar = $service->calendars->get($calendarId);
    echo "SUCCESS! Summary: " . $calendar->getSummary() . "\n";

} catch (\Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
