<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\ConsultationCreneau;
use App\Form\ReservationType;
use App\Repository\ConsultationRepository;
use App\Repository\ConsultationCreneauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConsultationController extends AbstractController
{
    // Route de redirection pour /consultation
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

        $consultationsMaman = array_filter(
            $consultations,
            fn($c) =>
            $c->getPour() === 'MAMAN' || $c->getPour() === 'LES_DEUX'
        );
        $consultationsBebe = array_filter(
            $consultations,
            fn($c) =>
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
        // Récupérer les médecins UNIQUES pour cette consultation (une photo par médecin via MAX)
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

    #[Route('/medecin/{medecin}/creneaux', name: 'app_medecin_creneaux', requirements: ['medecin' => '.+'])]
    public function creneaux(
        string $medecin,
        ConsultationCreneauRepository $creneauRepo
    ): Response {
        $medecinNom = $medecin;

        $creneaux = $creneauRepo->createQueryBuilder('cc')
            ->where('cc.nomMedecin = :medecin')
            ->andWhere('cc.statutReservation = :statut')
            ->andWhere('cc.dateDebut > :now')
            ->setParameter('medecin', $medecinNom)
            ->setParameter('statut', 'DISPONIBLE')
            ->setParameter('now', new \DateTime())
            ->orderBy('cc.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();

        $consultation = null;
        if (!empty($creneaux)) {
            $consultation = $creneaux[0]->getConsultation();
        }

        $creneauxParDate = [];
        foreach ($creneaux as $creneau) {
            $dateKey = $creneau->getDateDebut()->format('Y-m-d');
            if (!isset($creneauxParDate[$dateKey])) {
                $creneauxParDate[$dateKey] = [
                    'date' => $creneau->getDateDebut(),
                    'creneaux' => []
                ];
            }
            $creneauxParDate[$dateKey]['creneaux'][] = $creneau;
        }

        // Récupérer les infos du médecin (photo, spécialité) depuis un des créneaux
        $doctorInfo = $creneauRepo->createQueryBuilder('cc')
            ->select('MAX(cc.photoMedecin) as photo, MAX(cc.specialiteMedecin) as specialty, MAX(cc.descriptionMedecin) as description')
            ->where('cc.nomMedecin = :medecin')
            ->setParameter('medecin', $medecinNom)
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('consultation/creneaux.html.twig', [
            'medecin' => $medecinNom,
            'consultation' => $consultation,
            'creneauxParDate' => $creneauxParDate,
            'doctorInfo' => $doctorInfo,
        ]);
    }

    #[Route('/creneau/{id}/reserver', name: 'app_creneau_reserver')]
    public function reserver(
        ConsultationCreneau $creneau,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier si le créneau est déjà réservé
        if ($creneau->getStatutReservation() !== 'DISPONIBLE') {
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ce créneau a déjà été réservé.'
                ], 400);
            }
            $this->addFlash('error', 'Ce créneau a déjà été réservé.');
            return $this->redirectToRoute('app_consultations');
        }

        // Créer le formulaire
        $form = $this->createForm(ReservationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Récupérer les données
                $data = $form->getData();

                // MARQUER LE CRÉNEAU COMME INDISPONIBLE
                $creneau->setStatutReservation('RESERVE');

                // Créer la réservation client
                $reservation = new \App\Entity\ReservationClient();
                $reservation->setConsultationCreneau($creneau);
                $reservation->setNomClient($data['nom']);
                $reservation->setPrenomClient($data['prenom']);
                $reservation->setEmailClient($data['email']);
                $reservation->setTelephoneClient($data['telephone']);
                $reservation->setTypePatient($data['typePatient']);

                if ($data['typePatient'] === 'MAMAN') {
                    $reservation->setMoisGrossesse($data['moisGrossesse']);
                } elseif ($data['typePatient'] === 'BEBE') {
                    $reservation->setDateNaissanceBebe($data['dateNaissanceBebe']);
                }

                $reservation->setStatutReservation('CONFIRME');
                $reservation->setDateReservation(new \DateTime());
                $reference = 'RDV-' . strtoupper(uniqid());
                $reservation->setReference($reference);

                $reservation->setNotes($data['notes'] ?? null);
                $reservation->setCreatedAt(new \DateTimeImmutable());
                $reservation->setUpdatedAt(new \DateTimeImmutable());

                // Lier et sauvegarder
                $creneau->setReservation($reservation);
                $entityManager->persist($reservation);
                $entityManager->flush();

                // Réponse AJAX
                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'success' => true,
                        'message' => 'Réservation confirmée!',
                        'reference' => $reference,
                        'patientName' => $data['prenom'] . ' ' . $data['nom'],
                        'redirectUrl' => $this->generateUrl('app_reservation_confirmation', ['id' => $creneau->getId()])
                    ]);
                }

                return $this->redirectToRoute('app_reservation_confirmation', ['id' => $creneau->getId()]);
            } else {
                // Si le formulaire n'est pas valide et que c'est de l'AJAX
                if ($request->isXmlHttpRequest()) {
                    $errors = [];
                    foreach ($form->getErrors(true) as $error) {
                        $fieldName = $error->getOrigin()->getName();
                        $errors[$fieldName] = $error->getMessage();
                    }
                    return $this->json([
                        'success' => false,
                        'message' => 'Le formulaire contient des erreurs.',
                        'errors' => $errors
                    ], 400);
                }
            }
        }

        // Afficher le formulaire
        return $this->render('consultation/reserver.html.twig', [
            'creneau' => $creneau,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reservation/{id}/confirmation', name: 'app_reservation_confirmation')]
    public function confirmation(
        ConsultationCreneau $creneau,
        Request $request
    ): Response {
        if ($creneau->getStatutReservation() === 'DISPONIBLE') {
            $this->addFlash('error', 'Ce créneau n\'a pas été réservé.');
            return $this->redirectToRoute('app_consultations');
        }

        return $this->render('consultation/confirmation.html.twig', [
            'creneau' => $creneau,
        ]);
    }

    #[Route('/mes-rendez-vous', name: 'app_mes_rendezvous')]
    public function mesRendezVous(
        ConsultationCreneauRepository $creneauRepo,
        Request $request
    ): Response {
        // Pour tester, utilisez un email fixe
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
            'id' => $creneau->getId(),
            'disponible' => $creneau->getStatutReservation() === 'DISPONIBLE',
            'statut' => $creneau->getStatutReservation(),
            'medecin' => $creneau->getNomMedecin(),
            'date' => $creneau->getDateDebut()->format('d/m/Y H:i'),
            'reserved' => $creneau->getStatutReservation() !== 'DISPONIBLE'
        ]);
    }
}