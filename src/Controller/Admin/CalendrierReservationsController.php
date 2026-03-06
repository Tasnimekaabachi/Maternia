<?php

namespace App\Controller\Admin;

use App\Repository\OffreBabySitterRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class CalendrierReservationsController extends AbstractController
{
    #[Route('/calendrier-reservations', name: 'calendrier_reservations', methods: ['GET'])]
    public function index(Request $request, ReservationRepository $reservationRepo, OffreBabySitterRepository $offreRepo): Response
    {
        $offreId = $request->query->get('offre_id');
        $offres = $offreRepo->findBy([], ['nomBabysitter' => 'ASC']);
        $reservations = [];
        if ($offreId !== null && $offreId !== '') {
            $offre = $offreRepo->find((int) $offreId);
            if ($offre) {
                $reservations = $reservationRepo->findByOffre($offre);
            }
        } else {
            $reservations = $reservationRepo->findBy([], ['dateDebut' => 'ASC']);
        }
        return $this->render('admin/calendrier_reservations.html.twig', [
            'offres_babysitter' => $offres,
            'reservations' => $reservations,
            'offre_id_selected' => $offreId,
        ]);
    }
}
