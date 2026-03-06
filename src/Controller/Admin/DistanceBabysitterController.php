<?php

namespace App\Controller\Admin;

use App\Repository\OffreBabySitterRepository;
use App\Service\DistanceDurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class DistanceBabysitterController extends AbstractController
{
    #[Route('/distance-babysitter', name: 'distance_babysitter', methods: ['GET'])]
    public function index(OffreBabySitterRepository $offreBabySitterRepository): Response
    {
        return $this->render('admin/distance_babysitter.html.twig', [
            'offres_babysitter' => $offreBabySitterRepository->findAll(),
        ]);
    }

    /**
     * API : calcule la distance et le temps de trajet.
     * Paramètres (POST JSON ou GET) : origin (adresse ou "lat,lon"), destination (ville ou adresse ou "lat,lon").
     */
    #[Route('/distance-babysitter/calculate', name: 'distance_babysitter_calculate', methods: ['GET', 'POST'])]
    public function calculate(Request $request, DistanceDurationService $distanceService): JsonResponse
    {
        $origin = $request->get('origin', '');
        $destination = $request->get('destination', '');
        if ($request->getContentTypeFormat() === 'json') {
            $data = json_decode($request->getContent(), true) ?? [];
            $origin = $data['origin'] ?? $origin;
            $destination = $data['destination'] ?? $destination;
        }

        if ($origin === '' || $destination === '') {
            return $this->json(['error' => 'Origine et destination sont requis.'], 400);
        }

        $result = $distanceService->getDistanceAndDuration($origin, $destination);
        if ($result === null) {
            return $this->json(['error' => 'Service indisponible.'], 502);
        }
        if (isset($result['error'])) {
            return $this->json($result, 422);
        }

        return $this->json($result);
    }
}
