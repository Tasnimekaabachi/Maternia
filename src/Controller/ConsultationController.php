<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\ConsultationCreneau;
use App\Form\ReservationType;
use App\Repository\ConsultationRepository;
use App\Repository\ConsultationCreneauRepository;
use App\Service\GoogleMeetService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConsultationController extends AbstractController
{
    #[Route('/consultation', name: 'app_consultation_redirect')]
    public function redirectToConsultations(): Response
    {
        return $this->redirectToRoute('app_consultations');
    }

    #[Route('/consultations', name: 'app_consultations')]
    public function index(Request $request, ConsultationRepository $consultationRepo): Response
    {
        $searchTerm = $request->query->get('q');
        $consultations = $consultationRepo->searchActive($searchTerm);

        $consultationsMaman = array_filter($consultations, fn($c) =>
            $c->getPour() === 'MAMAN' || $c->getPour() === 'LES_DEUX'
        );
        $consultationsBebe = array_filter($consultations, fn($c) =>
            $c->getPour() === 'BEBE' || $c->getPour() === 'LES_DEUX'
        );

        return $this->render('consultation/index.html.twig', [
            'consultationsMaman' => $consultationsMaman,
            'consultationsBebe' => $consultationsBebe,
            'searchTerm' => $searchTerm ? trim($searchTerm) : null,
            'searchResultCount' => \count($consultations),
        ]);
    }

    #[Route('/consultation/{id}/medecins', name: 'app_consultation_medecins')]
    public function medecins(Consultation $consultation, ConsultationCreneauRepository $creneauRepo): Response
    {
        $medecins = $creneauRepo->createQueryBuilder('cc')
            ->select('cc.nomMedecin', 'MAX(cc.descriptionMedecin) AS descriptionMedecin', 'MAX(cc.photoMedecin) AS photoMedecin', 'MAX(cc.specialiteMedecin) AS specialiteMedecin')
            ->where('cc.consultation = :consultation')
            ->andWhere('cc.dateDebut > :now')
            ->setParameter('consultation', $consultation)
            ->setParameter('now', new \DateTime())
            ->groupBy('cc.nomMedecin')
            ->orderBy('cc.nomMedecin', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('consultation/medecins.html.twig', [
            'consultation' => $consultation,
            'medecins' => $medecins,
        ]);
    }

    #[Route('/medecin/{medecin}/creneaux', name: 'app_medecin_creneaux')]
    public function creneaux(
        string $medecin,
        ConsultationCreneauRepository $creneauRepo
    ): Response {
        $medecinNom = urldecode($medecin);

        /** @var ConsultationCreneau[] $creneaux */
        $creneaux = $creneauRepo->createQueryBuilder('cc')
            ->leftJoin('cc.reservation', 'r')
            ->where('cc.nomMedecin = :medecin')
            ->andWhere('cc.statutReservation = :statut')
            ->andWhere('cc.dateDebut > :now')
            ->andWhere('r.id IS NULL')
            ->setParameter('medecin', $medecinNom)
            ->setParameter('statut', 'DISPONIBLE')
            ->setParameter('now', new \DateTime())
            ->orderBy('cc.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();

        $consultation = null;
        if (!empty($creneaux)) {
            $first = $creneaux[0];
            if ($first instanceof ConsultationCreneau) {
                $consultation = $first->getConsultation();
            }
        }

        $creneauxParDate = [];
        foreach ($creneaux as $creneau) {
            if (!$creneau instanceof ConsultationCreneau) {
                continue;
            }
            $dateKey = $creneau->getDateDebut()?->format('Y-m-d') ?? 'unknown';
            if (!isset($creneauxParDate[$dateKey])) {
                $creneauxParDate[$dateKey] = [
                    'date' => $creneau->getDateDebut(),
                    'creneaux' => []
                ];
            }
            $creneauxParDate[$dateKey]['creneaux'][] = $creneau;
        }

        return $this->render('consultation/creneaux.html.twig', [
            'medecin' => $medecinNom,
            'consultation' => $consultation,
            'creneauxParDate' => $creneauxParDate,
        ]);
    }

    #[Route('/creneau/{id}/reserver', name: 'app_creneau_reserver')]
    public function reserver(
        ConsultationCreneau $creneau,
        Request $request,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
        GoogleMeetService $googleMeetService
    ): Response {
        if ($creneau->getStatutReservation() !== 'DISPONIBLE') {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Ce créneau a déjà été réservé.'], 400);
            }
            $this->addFlash('error', 'Ce créneau a déjà été réservé.');
            return $this->redirectToRoute('app_consultations');
        }

        if ($creneau->getReservation() !== null) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Ce créneau a déjà une réservation associée.'], 400);
            }
            $this->addFlash('error', 'Ce créneau a déjà une réservation associée.');
            return $this->redirectToRoute('app_consultations');
        }

        $form = $this->createForm(ReservationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    /** @var array<string, mixed> $data */
                    $data = $form->getData();

                    $nom         = isset($data['nom'])         && is_string($data['nom'])         ? $data['nom']         : '';
                    $prenom      = isset($data['prenom'])      && is_string($data['prenom'])      ? $data['prenom']      : '';
                    $email       = isset($data['email'])       && is_string($data['email'])       ? $data['email']       : '';
                    $telephone   = isset($data['telephone'])   && is_string($data['telephone'])   ? $data['telephone']   : '';
                    $typePatient = isset($data['typePatient']) && is_string($data['typePatient']) ? $data['typePatient'] : '';
                    $notes       = isset($data['notes'])       && is_string($data['notes'])       ? $data['notes']       : null;
                    $receiveSms  = !empty($data['receiveSms']);

                    $creneau->setStatutReservation('RESERVE');

                    $reservation = new \App\Entity\ReservationClient();
                    $reservation->setConsultationCreneau($creneau);
                    $reservation->setNomClient($nom);
                    $reservation->setPrenomClient($prenom);
                    $reservation->setEmailClient($email);
                    $reservation->setTelephoneClient($telephone);
                    $reservation->setTypePatient($typePatient);

                    if ($typePatient === 'MAMAN') {
                        $moisGrossesse = isset($data['moisGrossesse']) && is_int($data['moisGrossesse']) ? $data['moisGrossesse'] : null;
                        $reservation->setMoisGrossesse($moisGrossesse);
                    } elseif ($typePatient === 'BEBE') {
                        $dateNaissance = isset($data['dateNaissanceBebe']) && $data['dateNaissanceBebe'] instanceof \DateTimeInterface ? $data['dateNaissanceBebe'] : null;
                        $reservation->setDateNaissanceBebe($dateNaissance);
                    }

                    $reservation->setStatutReservation('CONFIRME');
                    $reservation->setDateReservation(new \DateTime());
                    $reference = 'RDV-' . strtoupper(uniqid());
                    $reservation->setReference($reference);
                    $reservation->setNotes($notes);
                    $reservation->setCreatedAt(new \DateTimeImmutable());
                    $reservation->setUpdatedAt(new \DateTimeImmutable());

                    $creneau->setReservation($reservation);
                    $entityManager->persist($reservation);
                    $entityManager->flush();

                    // --- GOOGLE MEET (auto) + fallback lien fixe ---
                    $meetLink = null;
                    try {
                        $meetStatus = $googleMeetService->createMeetLink($reservation);
                        if (($meetStatus['success'] ?? false) && !empty($meetStatus['meetLink'])) {
                            $meetLink = is_string($meetStatus['meetLink']) ? $meetStatus['meetLink'] : null;
                            $this->addFlash('info', 'Lien Meet généré avec succès.');
                        }
                    } catch (\Throwable) {
                        // fallback ci-dessous
                    }

                    if (!$meetLink) {
                        try {
                            $fallbackLink = $this->getParameter('app.online_meet_link');
                            $meetLink = is_string($fallbackLink) ? $fallbackLink : null;
                        } catch (\Throwable) {
                            $meetLink = null;
                        }
                    }

                    // --- ENVOI DES NOTIFICATIONS ---
                    try {
                        $notificationService->sendConfirmationEmail($reservation, $meetLink);
                        if ($receiveSms) {
                            $notificationService->sendConfirmationSms($reservation, $meetLink);
                        }
                    } catch (\Throwable) {
                        // On continue pour ne pas bloquer l'utilisateur
                    }

                    if ($request->isXmlHttpRequest()) {
                        return $this->json([
                            'success'     => true,
                            'message'     => 'Réservation confirmée!',
                            'reference'   => $reference,
                            'patientName' => $prenom . ' ' . $nom,
                            'meetLink'    => $meetLink,
                            'redirectUrl' => $this->generateUrl('app_reservation_confirmation', ['id' => $creneau->getId()])
                        ]);
                    }

                    return $this->redirectToRoute('app_reservation_confirmation', ['id' => $creneau->getId()]);

                } catch (\Throwable $e) {
                    if ($request->isXmlHttpRequest()) {
                        $debugMessage = null;
                        try {
                            if ($this->getParameter('kernel.environment') === 'dev') {
                                $debugMessage = $e->getMessage();
                            }
                        } catch (\Throwable) {
                            // ignore
                        }
                        return $this->json([
                            'success' => false,
                            'message' => 'Erreur interne du serveur lors de la réservation.',
                            'debug'   => $debugMessage,
                        ], 500);
                    }
                    throw $e;
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $errors = [];
                    foreach ($form->getErrors(true) as $error) {
                        $origin    = $error->getOrigin();
                        $fieldName = $origin !== null ? $origin->getName() : 'global';
                        $errors[$fieldName] = $error->getMessage();
                    }
                    return $this->json([
                        'success' => false,
                        'message' => 'Le formulaire contient des erreurs.',
                        'errors'  => $errors
                    ], 400);
                }
            }
        }

        return $this->render('consultation/reserver.html.twig', [
            'creneau' => $creneau,
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/reservation/{id}/confirmation', name: 'app_reservation_confirmation')]
    public function confirmation(ConsultationCreneau $creneau, Request $request): Response
    {
        if ($creneau->getStatutReservation() === 'DISPONIBLE') {
            $this->addFlash('error', 'Ce créneau n\'a pas été réservé.');
            return $this->redirectToRoute('app_consultations');
        }

        return $this->render('consultation/confirmation.html.twig', [
            'creneau' => $creneau,
        ]);
    }

    #[Route('/mes-rendez-vous', name: 'app_mes_rendezvous')]
    public function mesRendezVous(ConsultationCreneauRepository $creneauRepo, Request $request): Response
    {
        $email = $request->getSession()->get('user_email', 'test@example.com');

        $creneauxReserves = $creneauRepo->createQueryBuilder('c')
            ->join('c.reservation', 'r')
            ->where('r.emailClient = :email')
            ->andWhere('c.statutReservation != :statut')
            ->setParameter('email', $email)
            ->setParameter('statut', 'DISPONIBLE')
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('consultation/mes_rendezvous.html.twig', [
            'creneauxReserves' => $creneauxReserves,
        ]);
    }

    #[Route('/api/creneau/{id}/status', name: 'api_creneau_status')]
    public function creneauStatus(ConsultationCreneau $creneau): Response
    {
        return $this->json([
            'id'         => $creneau->getId(),
            'disponible' => $creneau->getStatutReservation() === 'DISPONIBLE',
            'statut'     => $creneau->getStatutReservation(),
            'medecin'    => $creneau->getNomMedecin(),
            'date'       => $creneau->getDateDebut()?->format('d/m/Y H:i') ?? '',
            'reserved'   => $creneau->getStatutReservation() !== 'DISPONIBLE'
        ]);
    }
}