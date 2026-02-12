<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function suiviGrossesse(): Response
    {
        return $this->render('admin/suivi_grossesse.html.twig');
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
        // Redirige vers la liste des offres babysitter qui alimente le template admin/profil_bebe.html.twig
        return $this->redirectToRoute('app_offre_baby_sitter_index');
    }
}
