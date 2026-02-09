<?php

namespace App\Controller\Admin;

use App\Repository\MamanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class DashboardController extends AbstractController
{
    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/suivi-grossesse', name: 'suivi_grossesse', methods: ['GET'])]
    public function suiviGrossesse(Request $request, MamanRepository $mamanRepository): Response
    {
        $groupeSanguin = $request->query->get('groupe_sanguin');
        $tri = $request->query->get('tri', 'date');
        $ordre = $request->query->get('ordre', 'DESC');
        $sortBy = $tri === 'taille' ? MamanRepository::SORT_TAILLE : ($tri === 'poids' ? MamanRepository::SORT_POIDS : MamanRepository::SORT_DATE);
        $mamans = $mamanRepository->findForAdmin($groupeSanguin, $sortBy, $ordre);
        $statsGroupe = $mamanRepository->getStatsByGroupeSanguin();
        $statsFumeur = $mamanRepository->getStatsByFumeur();
        return $this->render('admin/suivi_grossesse.html.twig', [
            'mamans' => $mamans,
            'stats_groupe_sanguin' => $statsGroupe,
            'stats_fumeur' => $statsFumeur,
            'filtre_groupe' => $groupeSanguin,
            'tri' => $tri,
            'ordre' => $ordre,
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
