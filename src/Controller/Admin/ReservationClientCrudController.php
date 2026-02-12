<?php

namespace App\Controller\Admin;

use App\Entity\ReservationClient;
use App\Form\ReservationClientType;
use App\Repository\ReservationClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            'total' => count($reservations),
            'confirmed' => count(array_filter($reservations, fn($r) => $r->getStatutReservation() === 'CONFIRME')),
            'pending' => count(array_filter($reservations, fn($r) => $r->getStatutReservation() === 'DISPONIBLE' || $r->getStatutReservation() === 'RESERVE')),
            'bebe' => count(array_filter($reservations, fn($r) => $r->getTypePatient() === 'BEBE')),
            'maman' => count(array_filter($reservations, fn($r) => $r->getTypePatient() === 'MAMAN')),
        ];

        // Données pour le graphique (6 derniers mois)
        $chartData = $this->getChartData($repository);

        return $this->render('admin/reservation_client/index.html.twig', [
            'reservations' => $reservations,
            'stats' => $stats,
            'chartData' => $chartData,
            'currentSort' => $sort,
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
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation créée avec succès.');
            return $this->redirectToRoute('app_admin_reservation_client_index');
        }

        return $this->render('admin/reservation_client/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
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
        }

        return $this->render('admin/reservation_client/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
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
}
