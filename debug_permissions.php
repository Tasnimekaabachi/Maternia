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
$targetId = getEnvVar('GOOGLE_MEET_CALENDAR_ID');

echo "Robot Email from JSON:\n";
$json = json_decode(file_get_contents($credentialsPath), true);
echo $json['client_email'] . "\n\n";

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR]);
    $service = new Google\Service\Calendar($client);

    echo "--- Checking Calendar ID: $targetId ---\n";
    try {
        $cal = $service->calendars->get($targetId);
        echo "SUCCESS: Found calendar '{$cal->getSummary()}'\n";
    } catch (Exception $e) {
        echo "FAILURE on direct get: " . $e->getMessage() . "\n";
    }

    echo "\n--- Listing Accessible Calendars ---\n";
    $list = $service->calendarList->listCalendarList();
    foreach ($list->getItems() as $item) {
        echo "ID: " . $item->getId() . " | Summary: " . $item->getSummary() . "\n";
    }
    if (count($list->getItems()) == 0) {
        echo "No calendars in list.\n";
    }

} catch (Exception $e) {
    echo "GLOBAL ERROR: " . $e->getMessage() . "\n";
}
