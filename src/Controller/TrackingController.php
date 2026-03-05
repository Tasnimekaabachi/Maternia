<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page publique de suivi des colis DHL.
 */
final class TrackingController extends AbstractController
{
    #[Route('/suivi-colis', name: 'app_tracking', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/tracking.html.twig');
    }
}
