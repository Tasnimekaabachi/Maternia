<?php

namespace App\Controller\Admin;

use App\Entity\ReservationClient;
use App\Form\ReservationClientType;
use App\Repository\ReservationClientRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reservations')]
class ReservationClientCrudController extends AbstractController
{
    #[Route('/', name: 'app_admin_reservation_client_index', methods: ['GET'])]
    public function index(Request $request, ReservationClientRepository $repository): Response
    {
        $sort = $request->query->get('sort', 'createdAt');
        $direction = $request->query->get('direction', 'DESC');
        
        // Sécurisation du tri
        $allowedSorts = ['createdAt', 'nomClient', 'dateReservation', 'statutReservation', 'reference'];
        if (!in_array($sort, $allowedSorts)) { $sort = 'createdAt'; }
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) { $direction = 'DESC'; }

        $reservations = $repository->findBy([], [$sort => $direction]);
        
        // Stats pour le plateau
        $stats = [
            'total'     => count($reservations),
            'confirmed' => count(array_filter($reservations, fn($r) => $r->getStatutReservation() === 'CONFIRME')),
            'pending'   => count(array_filter($reservations, fn($r) => in_array($r->getStatutReservation(), ['DISPONIBLE', 'RESERVE']))),
            'bebe'      => count(array_filter($reservations, fn($r) => $r->getTypePatient() === 'BEBE')),
            'maman'     => count(array_filter($reservations, fn($r) => $r->getTypePatient() === 'MAMAN')),
        ];

        // Données pour le graphique (6 derniers mois)
        $chartData = $this->getChartData($repository);

        return $this->render('admin/reservation_client/index.html.twig', [
            'reservations'     => $reservations,
            'stats'            => $stats,
            'chartData'        => $chartData,
            'currentSort'      => $sort,
            'currentDirection' => $direction,
        ]);
    }

    private function getChartData(ReservationClientRepository $repository): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("first day of -$i months");
            $monthName = $date->format('M');
            $count = count($repository->createQueryBuilder('r')
                ->where('r.createdAt >= :start')
                ->andWhere('r.createdAt <= :end')
                ->setParameter('start', $date->modify('first day of this month')->setTime(0,0,0))
                ->setParameter('end', (clone $date)->modify('last day of this month')->setTime(23,59,59))
                ->getQuery()
                ->getResult());
            
            $data['labels'][] = $monthName;
            $data['values'][] = $count;
        }
        return $data;
    }

    #[Route('/new', name: 'app_admin_reservation_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new ReservationClient();
        $reservation->setDateReservation(new \DateTime());
        $reservation->setReference('RES-' . strtoupper(uniqid()));
        $reservation->setStatutReservation('RESERVE');

        $form = $this->createForm(ReservationClientType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creneau = $reservation->getConsultationCreneau();
            if ($creneau) {
                $creneau->setStatutReservation('RESERVE');
            }
            
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation créée avec succès. Référence : #' . $reservation->getReference());
            return $this->redirectToRoute('app_admin_reservation_client_edit', ['id' => $reservation->getId()]);
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez les corriger.');
        }

        return $this->render('admin/reservation_client/new.html.twig', [
            'reservation' => $reservation,
            'form'        => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_reservation_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationClient $reservation, EntityManagerInterface $entityManager): Response
    {
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        $form = $this->createForm(ReservationClientType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Réservation mise à jour avec succès.');
            return $this->redirectToRoute('app_admin_reservation_client_index');
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Échec de la mise à jour. Veuillez vérifier les erreurs du formulaire.');
        }

        return $this->render('admin/reservation_client/edit.html.twig', [
            'reservation' => $reservation,
            'form'        => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reservation_client_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationClient $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => true]);
            }
            $this->addFlash('success', 'Réservation supprimée.');
        }

        return $this->redirectToRoute('app_admin_reservation_client_index');
    }

    /**
     * Endpoint AJAX : envoyer email de confirmation avec lien Google Meet
     */
    #[Route('/{id}/send-notification', name: 'app_admin_reservation_send_notification', methods: ['POST'])]
    public function sendNotification(
        Request $request,
        ReservationClient $reservation,
        NotificationService $notificationService
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('notify_' . $reservation->getId(), $request->request->get('_token'))) {
            return $this->json(['success' => false, 'message' => 'Token de sécurité invalide.'], 403);
        }

        $type     = $request->request->get('type', 'email');      // 'email' | 'sms'
        $meetLink = trim($request->request->get('meet_link', ''));
        $email    = trim($request->request->get('custom_email', ''));
        $phone    = trim($request->request->get('custom_phone', ''));
        $smsType  = $request->request->get('sms_type', 'normal'); // 'normal' | 'meet'

        $results = [];

        if ($type === 'email' || $type === 'both') {
            // Utiliser email custom ou email de la réservation
            $targetEmail = !empty($email) ? $email : $reservation->getEmailClient();
            $result = $notificationService->sendConfirmationEmail(
                $reservation,
                !empty($meetLink) ? $meetLink : null,
                $targetEmail
            );
            $results['email'] = $result;
        }

        if ($type === 'sms' || $type === 'both') {
            // Pour SMS avec lien Meet, inclure le lien s'il y en a un
            $smsLink = ($smsType === 'meet' && !empty($meetLink)) ? $meetLink : null;
            $targetPhone = !empty($phone) ? $phone : $reservation->getTelephoneClient();
            $result = $notificationService->sendConfirmationSms(
                $reservation,
                $smsLink,
                $targetPhone
            );
            $results['sms'] = $result;
        }

        // Déterminer le succès global
        $allSuccess = !empty($results) && !in_array(false, array_column($results, 'success'));

        $messages = [];
        foreach ($results as $channel => $res) {
            $prefix = $channel === 'email' ? '📧' : '📱';
            $messages[] = $prefix . ' ' . $res['message'];
        }

        return $this->json([
            'success'  => $allSuccess,
            'message'  => implode("\n", $messages),
            'results'  => $results,
        ]);
    }
}
