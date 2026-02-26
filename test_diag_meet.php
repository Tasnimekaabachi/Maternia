<?php
/**
 * Diagnostic complet : test API + détection du problème Meet
 */
require_once __DIR__ . '/vendor/autoload.php';

$credentialsPath = __DIR__ . '/config/google/service-account.json';
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

echo "=== DIAGNOSTIC GOOGLE MEET ===\n\n";

if (!file_exists($credentialsPath)) {
    echo "❌ Credentials introuvables: $credentialsPath\n"; exit(1);
}
echo "✅ Fichier credentials trouvé\n";

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_EVENTS]);
    $service = new Google\Service\Calendar($client);
    echo "✅ Authentification Google OK\n";

    // Accès au calendrier
    try {
        $cal = $service->calendars->get($calendarId);
        echo "✅ Accès à l'agenda OK: \"" . $cal->getSummary() . "\"\n";
    } catch (\Exception $e) {
        echo "❌ Impossible d'accéder à l'agenda: " . $e->getMessage() . "\n";
        echo "\n=== ACTION REQUISE ===\n";
        echo "Vous devez partager l'agenda 'Maternia RDV' avec ce compte de service:\n";
        echo "📧 maternia-calendar@master-plateau-488504-m3.iam.gserviceaccount.com\n";
        echo "Permission: 'Apporter des modifications aux événements'\n";
        exit(1);
    }

    // Créer un événement SANS conférence d'abord
    $start = new DateTime('+1 hour');
    $end = (clone $start)->modify('+30 minutes');

    // Version simple sans solution key (laisse Google décider)
    $confReq = new Google\Service\Calendar\CreateConferenceRequest();
    $confReq->setRequestId('diag-' . uniqid('', true));

    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary' => 'DIAGNOSTIC Maternia Meet',
        'start'   => ['dateTime' => $start->format(DATE_RFC3339), 'timeZone' => 'Africa/Tunis'],
        'end'     => ['dateTime' => $end->format(DATE_RFC3339), 'timeZone' => 'Africa/Tunis'],
        'conferenceData' => $confData,
    ]);

    echo "\nCréation de l'événement test...\n";
    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    echo "✅ Événement créé (ID: " . $created->getId() . ")\n";

    // Attendre et vérifier
    $meetLink = null;
    for ($i = 1; $i <= 4; $i++) {
        sleep(3);
        $refreshed = $service->events->get($calendarId, $created->getId());
        $conf = $refreshed->getConferenceData();
        $meetLink = $refreshed->getHangoutLink();

        if (!$meetLink && $conf) {
            foreach ((array)$conf->getEntryPoints() as $ep) {
                if ($ep->getEntryPointType() === 'video') {
                    $meetLink = $ep->getUri();
                    break;
                }
            }
        }

        if ($meetLink) break;

        // Afficher le statut de la conférence
        $statusCode = $conf ? optional_chain($conf) : 'Pas de ConferenceData';
        echo "  Tentative $i/4 - Pas encore de lien. ConferenceData: " . ($conf ? "OUI" : "NON") . "\n";
        if ($conf && $conf->getCreateRequest()) {
            $status = $conf->getCreateRequest()->getStatus();
            echo "  Status code: " . ($status ? $status->getStatusCode() : 'null') . "\n";
        }
    }

    // Nettoyer
    $service->events->delete($calendarId, $created->getId());

    if ($meetLink) {
        echo "\n✅✅ SUCCÈS! Lien Meet généré:\n$meetLink\n\n";
        echo "=== CONFIGURATION CORRECTE ===\n";
        echo "GOOGLE_MEET_CREDENTIALS_PATH=$credentialsPath\n";
        echo "GOOGLE_MEET_CALENDAR_ID=$calendarId\n";
    } else {
        echo "\n❌ La conférence Meet n'est pas générée.\n";
        echo "\n=== CAUSES POSSIBLES ===\n";
        echo "1. L'agenda doit être dans un espace de travail Google Workspace (pas un compte Gmail personnel)\n";
        echo "2. L'admin Google Workspace doit activer 'Google Meet' pour les comptes de service\n";
        echo "3. Le compte de service doit avoir la permission 'Apporter des modifications aux événements'\n";
        echo "\n=== SOLUTION ALTERNATIVE : GOOGLE MEET ADDON API ===\n";
        echo "Utiliser l'API Meet directe: https://developers.google.com/meet/api\n";
    }

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
