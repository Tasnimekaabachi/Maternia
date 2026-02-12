<?php

namespace App\Controller\Admin;

use App\Repository\ConsultationCreneauRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ConsultationCreneauRepository $consultationCreneauRepository
    ) {
    }

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        EventRepository $eventRepository,
        ProduitRepository $produitRepository,
        CommandeRepository $commandeRepository
    ): Response {

        // ===== Marketplace Stats =====
        $nbProduits = $produitRepository->count([]);
        $nbCommandesAttente = $commandeRepository->countByStatut('En attente');
        $nbCommandesValidees = $commandeRepository->countByStatut('Validée');
        $nbCommandesAnnulees = $commandeRepository->countByStatut('Annulée');
        $chiffreAffaires = $commandeRepository->chiffreAffairesValidees();
        $topProduits = $commandeRepository->topProduitsCommandes(5);

        // ===== Events =====
        $eventCount = $eventRepository->count([]);

        // Fetch recent items (guaranteeing a mix of types)
        $recentCreneaux = $this->consultationCreneauRepository->findBy([], ['id' => 'DESC'], 10);
        $recentEvents = $eventRepository->findBy([], ['id' => 'DESC'], 10);
        $recentProduits = $produitRepository->findBy([], ['id' => 'DESC'], 10);
        $recentCommandes = $commandeRepository->findBy([], ['id' => 'DESC'], 10);

        $activityLists = [
            'commande' => [],
            'product' => [],
            'event' => [],
            'creneau' => []
        ];

        foreach ($recentCommandes as $com) {
            $activityLists['commande'][] = [
                'type' => 'commande',
                'title' => 'Commande #' . $com->getId(),
                'context' => $com->getStatut() . ' - ' . number_format($com->getTotal(), 2) . ' TND',
                'date' => $com->getDateCommande(),
                'url' => '#',
                'icon' => 'fa-shopping-cart',
                'badge_text' => 'Commande',
                'badge_class' => 'bg-success-light',
                'id' => $com->getId()
            ];
        }

        foreach ($recentProduits as $p) {
            $activityLists['product'][] = [
                'type' => 'product',
                'title' => $p->getNom(),
                'context' => 'Stock: ' . $p->getStock() . ' - ' . number_format($p->getPrix(), 2) . ' TND',
                'date' => new \DateTime(),
                'url' => $this->generateUrl('app_produit_show', ['id' => $p->getId()]),
                'icon' => 'fa-box',
                'badge_text' => 'Produit',
                'badge_class' => 'bg-warning-light',
                'id' => $p->getId()
            ];
        }

        foreach ($recentEvents as $e) {
            $activityLists['event'][] = [
                'type' => 'event',
                'title' => $e->getTitle(),
                'context' => $e->getEventCat()->getName(),
                'date' => $e->getStartAt(),
                'url' => $this->generateUrl('app_event_show', ['id' => $e->getId()]),
                'icon' => 'fa-calendar-alt',
                'badge_text' => 'Événement',
                'badge_class' => 'bg-pink-light',
                'id' => $e->getId()
            ];
        }

        foreach ($recentCreneaux as $c) {
            $activityLists['creneau'][] = [
                'type' => 'creneau',
                'title' => $c->getNomMedecin(),
                'context' => $c->getSpecialiteMedecin() ?: $c->getConsultation()->getCategorie(),
                'date' => $c->getDateDebut(),
                'url' => $this->generateUrl(
                    'app_admin_consultation_creneau_show',
                    ['id' => $c->getId()]
                ),
                'icon' => 'fa-stethoscope',
                'badge_text' => 'Créneau',
                'badge_class' => 'bg-info-light',
                'id' => $c->getId()
            ];
        }

        // Interleave the lists to ensure relative recency visibility
        $activity = [];
        $maxCount = max(
            count($activityLists['commande']),
            count($activityLists['product']),
            count($activityLists['event']),
            count($activityLists['creneau'])
        );

        for ($i = 0; $i < $maxCount; $i++) {
            if (isset($activityLists['commande'][$i]))
                $activity[] = $activityLists['commande'][$i];
            if (isset($activityLists['product'][$i]))
                $activity[] = $activityLists['product'][$i];
            if (isset($activityLists['event'][$i]))
                $activity[] = $activityLists['event'][$i];
            if (isset($activityLists['creneau'][$i]))
                $activity[] = $activityLists['creneau'][$i];

            if (count($activity) >= 12)
                break;
        }

        return $this->render('admin/dashboard.html.twig', [
            // Marketplace
            'nbProduits' => $nbProduits,
            'nbCommandesAttente' => $nbCommandesAttente,
            'nbCommandesValidees' => $nbCommandesValidees,
            'nbCommandesAnnulees' => $nbCommandesAnnulees,
            'chiffreAffaires' => $chiffreAffaires,
            'topProduits' => $topProduits,

            // Existing dashboard data
            'eventCount' => $eventCount,
            'creneaux_ce_mois' => $this->consultationCreneauRepository->countCeMois(),
            'recent_activity' => $activity,
        ]);
    }

    #[Route('/suivi-grossesse', name: 'suivi_grossesse', methods: ['GET'])]
    public function suiviGrossesse(): Response
    {
        return $this->render('admin/suivi_grossesse.html.twig');
    }

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

    #[Route('/profil-bebe', name: 'profil_bebe', methods: ['GET'])]
    public function profilBebe(): Response
    {
        return $this->render('admin/profil_bebe.html.twig');
    }
}
