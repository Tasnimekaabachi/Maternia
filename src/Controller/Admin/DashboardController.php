<?php

namespace App\Controller\Admin;

use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class DashboardController extends AbstractController
{
    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        ProduitRepository $produitRepository,
        CommandeRepository $commandeRepository
    ): Response {
        $nbProduits = $produitRepository->count([]);
        $nbCommandesAttente = $commandeRepository->countByStatut('En attente');
        $nbCommandesValidees = $commandeRepository->countByStatut('Validée');
        $nbCommandesAnnulees = $commandeRepository->countByStatut('Annulée');
        $chiffreAffaires = $commandeRepository->chiffreAffairesValidees();
        $topProduits = $commandeRepository->topProduitsCommandes(5);

        return $this->render('admin/dashboard.html.twig', [
            'nbProduits' => $nbProduits,
            'nbCommandesAttente' => $nbCommandesAttente,
            'nbCommandesValidees' => $nbCommandesValidees,
            'nbCommandesAnnulees' => $nbCommandesAnnulees,
            'chiffreAffaires' => $chiffreAffaires,
            'topProduits' => $topProduits,
        ]);
    }

    #[Route('/suivi-grossesse', name: 'suivi_grossesse', methods: ['GET'])]
    public function suiviGrossesse(): Response
    {
        return $this->render('admin/suivi_grossesse.html.twig');
    }

    // Ancienne route conservée pour éviter conflit avec AdminProduitController.
    // La marketplace admin officielle est gérée par `App\Controller\AdminProduitController`.
    #[Route('/marketplace-legacy', name: 'marketplace_legacy', methods: ['GET'])]
    public function marketplaceLegacy(): Response
    {
        return $this->redirectToRoute('admin_marketplace');
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
