<?php
require_once __DIR__ . '/vendor/autoload.php';

$credentialsPath = 'C:\\Users\\OrdiOne\\Desktop\\Maternia\\config\\google\\service-account.json';
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_READONLY]);
    $service = new Google\Service\Calendar($client);

    echo "=== INSPECTION DES SOLUTIONS DE CONFÉRENCE ===\n";
    $cal = $service->calendarList->get($calendarId);
    $solutions = $cal->getConferenceProperties()->getAllowedConferenceSolutionTypes();
    
    if (empty($solutions)) {
        echo "❌ AUCUNE SOLUTION DE CONFÉRENCE AUTORISÉE SUR CET AGENDA.\n";
        echo "Vous devez activer Google Meet dans les réglages de cet agenda.\n";
    } else {
        echo "Solutions autorisées : " . implode(', ', $solutions) . "\n";
    }

} catch (\Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
