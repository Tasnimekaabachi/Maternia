<?php

namespace App\Controller;

use App\Repository\OffreBabySitterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
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

    #[Route('/babysitting', name: 'app_babysitting')]
    public function babysitting(OffreBabySitterRepository $repository): Response
    {
        $offres = $repository->findAll();

        return $this->render('pages/babysitting.html.twig', [
            'offre_baby_sitters' => $offres,
        ]);
    }

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('pages/services.html.twig');
    }

    #[Route('/rendez-vous', name: 'app_appointment')]
    public function appointment(): Response
    {
        return $this->render('pages/appointment.html.twig');
    }

    #[Route('/evenements', name: 'app_events')]
    public function events(): Response
    {
        return $this->render('pages/events.html.twig');
    }
}
