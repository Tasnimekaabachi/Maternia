<?php
/**
 * Test précis : inspecte exactement l'état de la conférence après création
 */
require_once __DIR__ . '/vendor/autoload.php';

$credentialsPath = __DIR__ . '/config/google/service-account.json';
$calendarId = 'd379a10f47813cadce4bf3be8b40644c57e5846c86ec36c1de294838fe867467@group.calendar.google.com';

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([Google\Service\Calendar::CALENDAR_EVENTS]);
    $service = new Google\Service\Calendar($client);

    $start = new DateTime('+1 day');
    $end = (clone $start)->modify('+30 minutes');

    // Création de l'événement avec conférence
    $confReq = new Google\Service\Calendar\CreateConferenceRequest();
    $confReq->setRequestId('maternia-diag-' . time());

    $confData = new Google\Service\Calendar\ConferenceData();
    $confData->setCreateRequest($confReq);

    $event = new Google\Service\Calendar\Event([
        'summary'        => 'DIAG Meet - ' . date('H:i:s'),
        'start'          => ['dateTime' => $start->format(DATE_RFC3339), 'timeZone' => 'Africa/Tunis'],
        'end'            => ['dateTime' => $end->format(DATE_RFC3339), 'timeZone' => 'Africa/Tunis'],
        'conferenceData' => $confData,
    ]);

    $created = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    $eventId = $created->getId();
    echo "✅ Événement créé: $eventId\n";
    echo "HangoutLink immédiat: " . ($created->getHangoutLink() ?: 'NONE') . "\n\n";

    // Inspecter en détail après création
    $conf = $created->getConferenceData();
    if ($conf) {
        echo "ConferenceData présent:\n";
        $req = $conf->getCreateRequest();
        if ($req) {
            $status = $req->getStatus();
            echo "  - Status: " . ($status ? $status->getStatusCode() : 'null') . "\n";
            // pending = Google n'a pas encore assigné Meet
            // success = Meet assigné
            // failure = impossible de créer Meet
        }
        $solution = $conf->getConferenceSolution();
        if ($solution) {
            echo "  - Solution: " . $solution->getName() . "\n";
        }
        echo "  - EntryPoints: " . count($conf->getEntryPoints() ?? []) . "\n";
    } else {
        echo "❌ Aucune ConferenceData dans la réponse\n";
    }

    // Attendre et reassurcir
    echo "\nAttente 3 secondes...\n";
    sleep(3);
    $refreshed = $service->events->get($calendarId, $eventId);

    $hangout = $refreshed->getHangoutLink();
    echo "HangoutLink après 3s: " . ($hangout ?: 'NONE') . "\n";

    $conf2 = $refreshed->getConferenceData();
    if ($conf2) {
        $req2 = $conf2->getCreateRequest();
        if ($req2 && $req2->getStatus()) {
            $statusCode = $req2->getStatus()->getStatusCode();
            echo "Status conférence: $statusCode\n";
            
            if ($statusCode === 'failure') {
                echo "\n❌ PROBLÈME IDENTIFIÉ :\n";
                echo "Google refuse de créer un Meet pour ce calendrier.\n";
                echo "Cause probable : le compte Google associé à cet agenda\n";
                echo "n'a pas Google Meet activé (besoin de Google Workspace).\n\n";
                echo "SOLUTION : Utilisez l'API Google Meet directement.\n";
            } elseif ($statusCode === 'success') {
                echo "\n✅ Statut SUCCESS!\n";
                // Chercher les entry points
                foreach ($conf2->getEntryPoints() as $ep) {
                    echo "EntryPoint [" . $ep->getEntryPointType() . "]: " . $ep->getUri() . "\n";
                }
            } elseif ($statusCode === 'pending') {
                echo "\n⏳ Toujours en attente (pending)...\n";
            }
        }
        $solution2 = $conf2->getConferenceSolution();
        if ($solution2) {
            echo "Solution finale: " . $solution2->getName() . " / key: " . $solution2->getKey()->getType() . "\n";
        }
    }

    // Nettoyage
    $service->events->delete($calendarId, $eventId);
    echo "\n(Événement supprimé)\n";

} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
