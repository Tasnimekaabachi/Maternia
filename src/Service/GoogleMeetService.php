<?php

namespace App\Service;

use App\Entity\ReservationClient;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceSolutionKey;
use Psr\Log\LoggerInterface;

class GoogleMeetService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ?string $credentialsPath = null,
        private readonly ?string $calendarId = null
    ) {
    }

    public function createMeetLink(ReservationClient $reservation): array
    {
        $credentialsPath = (string) ($this->credentialsPath ?? '');
        $calendarId = (string) ($this->calendarId ?? '');

        if (trim($credentialsPath) === '' || trim($calendarId) === '') {
            return [
                'success' => false,
                'message' => 'Google Meet non configuré (GOOGLE_MEET_CREDENTIALS_PATH / GOOGLE_MEET_CALENDAR_ID manquants).',
            ];
        }

        $creneau = $reservation->getConsultationCreneau();
        if ($creneau === null || $creneau->getDateDebut() === null) {
            return [
                'success' => false,
                'message' => 'Impossible de générer le Meet: créneau/dates manquants.',
            ];
        }

        $start = $creneau->getDateDebut();
        $end = $creneau->getDateFin();
        if ($end === null) {
            $end = (clone $start)->modify('+30 minutes');
        }

        try {
            $client = new Client();
            $client->setApplicationName('Maternia');
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([Calendar::CALENDAR_EVENTS]);
            $client->setAccessType('offline');

            $service = new Calendar($client);

            $summary = sprintf(
                'Maternia - RDV #%s (%s %s)',
                $reservation->getReference(),
                $reservation->getPrenomClient(),
                $reservation->getNomClient()
            );

            $description = sprintf(
                "Référence: %s\nPatient: %s %s\nEmail: %s\nTéléphone: %s",
                $reservation->getReference(),
                $reservation->getPrenomClient(),
                $reservation->getNomClient(),
                (string) $reservation->getEmailClient(),
                (string) $reservation->getTelephoneClient()
            );

            $event = new Event([
                'summary' => $summary,
                'description' => $description,
                'start' => new EventDateTime([
                    'dateTime' => $start->format(DATE_RFC3339),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $end->format(DATE_RFC3339),
                ]),
            ]);

            $solutionKey = new ConferenceSolutionKey();
            $solutionKey->setType('hangoutsMeet');

            $conferenceRequest = new CreateConferenceRequest();
            $conferenceRequest->setRequestId('maternia-' . uniqid('', true));
            $conferenceRequest->setConferenceSolutionKey($solutionKey);

            $conferenceData = new ConferenceData();
            $conferenceData->setCreateRequest($conferenceRequest);

            $event->setConferenceData($conferenceData);

            $created = $service->events->insert(
                $calendarId,
                $event,
                ['conferenceDataVersion' => 1]
            );

            $meetLink = null;
            $conf = $created->getConferenceData();
            if ($conf && $conf->getEntryPoints()) {
                foreach ($conf->getEntryPoints() as $ep) {
                    if ($ep->getEntryPointType() === 'video' && $ep->getUri()) {
                        $meetLink = $ep->getUri();
                        break;
                    }
                }
            }

            if (!$meetLink) {
                $hangoutLink = $created->getHangoutLink();
                if (is_string($hangoutLink) && $hangoutLink !== '') {
                    $meetLink = $hangoutLink;
                }
            }

            if (!$meetLink) {
                return [
                    'success' => false,
                    'message' => 'Événement créé, mais lien Google Meet introuvable.',
                    'eventId' => $created->getId(),
                ];
            }

            $this->logger->info('Google Meet créé', [
                'reservation' => $reservation->getReference(),
                'eventId' => $created->getId(),
                'meetLink' => $meetLink,
            ]);

            return [
                'success' => true,
                'message' => 'Lien Google Meet généré.',
                'meetLink' => $meetLink,
                'eventId' => $created->getId(),
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Erreur génération Google Meet', [
                'error' => $e->getMessage(),
                'reservation' => $reservation->getReference(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur Google Meet: ' . $e->getMessage(),
            ];
        }
    }
}
