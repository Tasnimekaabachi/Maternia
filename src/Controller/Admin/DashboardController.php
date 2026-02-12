<?php

namespace App\Controller\Admin;

use App\Repository\GrosesseRepository;
use App\Repository\MamanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class DashboardController extends AbstractController
{
    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(MamanRepository $mamanRepository, GrosesseRepository $grosesseRepository): Response
    {
        $totalMamans = $mamanRepository->count([]);
        $totalGrossesses = $grosesseRepository->count([]);
        $statsStatut = $grosesseRepository->getStatsByStatut();

        // On considère "suivis grossesse actifs" = grossesses en cours + à risque
        $suivisActifs = ($statsStatut['enCours'] ?? 0) + ($statsStatut['aRisque'] ?? 0);

        return $this->render('admin/dashboard.html.twig', [
            'total_mamans' => $totalMamans,
            'total_grossesses' => $totalGrossesses,
            'suivis_grossesse_actifs' => $suivisActifs,
        ]);
    }

    #[Route('/suivi', name: 'suivi', methods: ['GET'])]
    public function suivi(MamanRepository $mamanRepository, GrosesseRepository $grosesseRepository): Response
    {
        $totalMamans = $mamanRepository->count([]);
        $totalGrossesses = $grosesseRepository->count([]);
        $statsStatut = $grosesseRepository->getStatsByStatut();

        return $this->render('admin/suivi_choice.html.twig', [
            'total_mamans' => $totalMamans,
            'total_grossesses' => $totalGrossesses,
            'grossesses_en_cours' => $statsStatut['enCours'] ?? 0,
            'grossesses_terminees' => $statsStatut['terminee'] ?? 0,
        ]);
    }

    #[Route('/suivi-grossesse', name: 'suivi_grossesse', methods: ['GET'])]
    public function suiviGrossesse(MamanRepository $mamanRepository, GrosesseRepository $grosesseRepository): Response
    {
        $totalMamans = $mamanRepository->count([]);
        $totalGrossesses = $grosesseRepository->count([]);
        $statsStatut = $grosesseRepository->getStatsByStatut();

        return $this->render('admin/suivi_choice.html.twig', [
            'total_mamans' => $totalMamans,
            'total_grossesses' => $totalGrossesses,
            'grossesses_en_cours' => $statsStatut['enCours'] ?? 0,
            'grossesses_terminees' => $statsStatut['terminee'] ?? 0,
        ]);
    }

    #[Route('/marketplace', name: 'marketplace', methods: ['GET'])]
    public function marketplace(): Response
    {
        return $this->render('admin/marketplace.html.twig');
    }

    #[Route('/evenements', name: 'evenements', methods: ['GET'])]
    public function evenements(): Response
    {
        return $this->render('admin/evenements.html.twig');
    }

    #[Route('/consultations', name: 'consultations', methods: ['GET'])]
    public function consultations(): Response
    {
        return $this->render('admin/consultations.html.twig');
    }

    #[Route('/profil-bebe', name: 'profil_bebe', methods: ['GET'])]
    public function profilBebe(): Response
    {
        return $this->render('admin/profil_bebe.html.twig');
    }
}
