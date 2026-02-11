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

    // Supprimez la route consultations existante car maintenant
    // elle sera gérée par le CRUDController
    // #[Route('/consultations', name: 'consultations', methods: ['GET'])]
    // public function consultations(): Response
    // {
    //     return $this->render('admin/consultations.html.twig');
    // }

    // Gardez les autres routes...
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

    #[Route('/profil-bebe', name: 'profil_bebe', methods: ['GET'])]
    public function profilBebe(): Response
    {
        return $this->render('admin/profil_bebe.html.twig');
    }
}