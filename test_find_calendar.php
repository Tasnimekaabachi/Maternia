<?php
/**
 * Script pour trouver l'ID du calendrier "Maternia RDV"
 * et tester l'envoi d'un lien Google Meet
 */
require_once __DIR__ . '/vendor/autoload.php';

$credentialsPath = __DIR__ . '/config/google/service-account.json';

if (!file_exists($credentialsPath)) {
    echo "ERREUR: Fichier credentials introuvable: $credentialsPath\n";
    exit(1);
}

try {
    $client = new Google\Client();
    $client->setAuthConfig($credentialsPath);
    $client->setScopes([
        Google\Service\Calendar::CALENDAR,
        Google\Service\Calendar::CALENDAR_EVENTS,
    ]);

    $service = new Google\Service\Calendar($client);

    echo "=== LISTE DE VOS CALENDRIERS ===\n";
    $calendarList = $service->calendarList->listCalendarList();
    
    $materinaId = null;
    foreach ($calendarList->getItems() as $cal) {
        $summary = $cal->getSummary();
        $id = $cal->getId();
        echo "  - Nom: \"$summary\"\n";
        echo "    ID:  $id\n\n";
        
        // Chercher "Maternia RDV" (insensible à la casse)
        if (stripos($summary, 'Maternia') !== false || stripos($summary, 'RDV') !== false) {
            $materinaId = $id;
            echo "    *** CORRESPONDANCE TROUVÉE! ***\n\n";
        }
    }

    if ($materinaId) {
        echo "\n=== CALENDRIER MATERNIA RDV TROUVÉ ===\n";
        echo "ID: $materinaId\n\n";
        
        // Tester la création d'un événement Google Meet
        echo "=== TEST CRÉATION LIEN MEET ===\n";
        
        $start = new DateTime('+1 day');
        $end = (clone $start)->modify('+30 minutes');

        $solutionKey = new Google\Service\Calendar\ConferenceSolutionKey();
        $solutionKey->setType('hangoutsMeet');

        $confReq = new Google\Service\Calendar\CreateConferenceRequest();
        $confReq->setRequestId('maternia-test-' . time());
        $confReq->setConferenceSolutionKey($solutionKey);

        $confData = new Google\Service\Calendar\ConferenceData();
        $confData->setCreateRequest($confReq);

        $event = new Google\Service\Calendar\Event([
            'summary'        => 'TEST - Maternia Meet Link',
            'description'    => 'Test automatique de génération de lien Google Meet',
            'start'          => ['dateTime' => $start->format(DATE_RFC3339)],
            'end'            => ['dateTime' => $end->format(DATE_RFC3339)],
            'conferenceData' => $confData,
        ]);

        $created = $service->events->insert(
            $materinaId,
            $event,
            ['conferenceDataVersion' => 1]
        );

        echo "Événement créé, ID: " . $created->getId() . "\n";
        echo "En attente que Google génère le lien Meet...\n";

        $meetLink = null;
        for ($i = 0; $i < 5; $i++) {
            sleep(2);
            $refreshed = $service->events->get($materinaId, $created->getId());
            
            // Essai 1: hangoutLink
            $meetLink = $refreshed->getHangoutLink();
            
            // Essai 2: entryPoints
            if (!$meetLink) {
                $conf = $refreshed->getConferenceData();
                if ($conf && $conf->getEntryPoints()) {
                    foreach ($conf->getEntryPoints() as $ep) {
                        if ($ep->getEntryPointType() === 'video') {
                            $meetLink = $ep->getUri();
                            break;
                        }
                    }
                }
            }

            if ($meetLink) {
                echo "\n✅ SUCCÈS ! Lien Meet généré:\n$meetLink\n\n";
                break;
            } else {
                echo "  Tentative " . ($i + 1) . "/5 - Pas encore de lien...\n";
            }
        }

        // Supprimer l'événement de test
        $service->events->delete($materinaId, $created->getId());
        echo "(Événement test supprimé)\n";

        if (!$meetLink) {
            echo "\n❌ ÉCHEC: Aucun lien Meet généré après 5 tentatives.\n";
            echo "Vérifiez que le compte de service a bien l'autorisation 'Make changes to events' sur l'agenda '$materinaId'.\n";
        }

        echo "\n=== CONFIGURATION .ENV À UTILISER ===\n";
        echo "GOOGLE_MEET_CREDENTIALS_PATH=" . __DIR__ . "/config/google/service-account.json\n";
        echo "GOOGLE_MEET_CALENDAR_ID=$materinaId\n";

    } else {
        echo "\n⚠️  Aucun calendrier correspondant trouvé.\n";
        echo "Vérifiez que le compte de service a bien été partagé avec l'agenda 'Maternia RDV'.\n";
        echo "Email du compte de service: maternia-calendar@master-plateau-488504-m3.iam.gserviceaccount.com\n";
    }

} catch (\Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
