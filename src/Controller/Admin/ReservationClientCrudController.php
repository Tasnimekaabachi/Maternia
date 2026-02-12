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
    public function index(ReservationClientRepository $repository): Response
    {
        $reservations = $repository->findBy([], ['createdAt' => 'DESC']);
        
        // Stats pour le plateau
        $stats = [
            'total' => count($reservations),
            'confirmed' => count(array_filter($reservations, fn($r) => $r->getStatutReservation() === 'CONFIRME')),
            'pending' => count(array_filter($reservations, fn($r) => $r->getStatutReservation() === 'DISPONIBLE' || $r->getStatutReservation() === 'RESERVE')),
            'bebe' => count(array_filter($reservations, fn($r) => $r->getTypePatient() === 'BEBE')),
            'maman' => count(array_filter($reservations, fn($r) => $r->getTypePatient() === 'MAMAN')),
        ];

        return $this->render('admin/reservation_client/index.html.twig', [
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
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
