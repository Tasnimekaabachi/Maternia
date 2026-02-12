<?php

namespace App\Controller;

use App\Repository\ConsultationRepository;
use App\Repository\ConsultationCreneauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private ConsultationRepository $consultationRepository,
        private ConsultationCreneauRepository $creneauRepository
    ) {
    }

    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/marketplace', name: 'app_marketplace')]
    public function marketplace(): Response
    {
        return $this->render('pages/marketplace.html.twig');
    }

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('pages/services.html.twig');
    }

    #[Route('/rendez-vous', name: 'app_appointment')]
    public function appointment(): Response
    {
        return $this->render('pages/appointment.html.twig', [
            'consultationsMaman' => $this->consultationRepository->findByType('MAMAN'),
            'consultationsBebe' => $this->consultationRepository->findByType('BEBE'),
            'creneauxReserves' => $this->creneauRepository->findCreneauxReserves(),
        ]);
    }

    #[Route('/evenements', name: 'app_events')]
    public function events(): Response
    {
        return $this->render('pages/events.html.twig');
    }
}
