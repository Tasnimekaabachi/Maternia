<?php

namespace App\Controller\Admin;

use App\Repository\ConsultationCreneauRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\EventRepository;
use App\Repository\ConsultationRepository;
use App\Repository\MamanRepository;
use App\Repository\GrosesseRepository;
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
        CommandeRepository $commandeRepository,
        MamanRepository $mamanRepository,
        GrosesseRepository $grosesseRepository,
        ConsultationRepository $consultationRepository
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

        // ===== Suivi Grossesse Stats =====
        $totalMamans = $mamanRepository->count([]);
        $totalGrossesses = $grosesseRepository->count([]);
        $statsStatut = $grosesseRepository->getStatsByStatut();

        $suivisActifs =
            ($statsStatut['enCours'] ?? 0) +
            ($statsStatut['aRisque'] ?? 0);

        // ===== Recent Activity (Interleaved) =====
        $recentCreneaux = $this->consultationCreneauRepository->findBy([], ['id' => 'DESC'], 10);
        $recentEvents = $eventRepository->findBy([], ['id' => 'DESC'], 10);
        $recentProduits = $produitRepository->findBy([], ['id' => 'DESC'], 10);
        $recentMamans = $mamanRepository->findBy([], ['id' => 'DESC'], 10);
        $recentGrossesses = $grosesseRepository->findBy([], ['id' => 'DESC'], 10);
        $recentConsultations = $consultationRepository->findBy([], ['id' => 'DESC'], 10);
        $recentCommandes = $commandeRepository->findBy([], ['id' => 'DESC'], 10);

        $activityLists = [
            'event' => [],
            'creneau' => [],
            'product' => [],
            'maman' => [],
            'grossesse' => [],
            'consultation' => [],
            'commande' => []
        ];

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
                'url' => $this->generateUrl('app_admin_consultation_creneau_show', ['id' => $c->getId()]),
                'icon' => 'fa-stethoscope',
                'badge_text' => 'Créneau',
                'badge_class' => 'bg-info-light',
                'id' => $c->getId()
            ];
        }

        foreach ($recentProduits as $p) {
            $activityLists['product'][] = [
                'type' => 'product',
                'title' => $p->getNom(),
                'context' => $p->getPrix() . ' DT - Stock: ' . $p->getStock(),
                'url' => $this->generateUrl('admin_produit_edit', ['id' => $p->getId()]),
                'icon' => 'fa-box',
                'badge_text' => 'Produit',
                'badge_class' => 'bg-success-light',
                'id' => $p->getId()
            ];
        }

        foreach ($recentMamans as $m) {
            $activityLists['maman'][] = [
                'type' => 'maman',
                'title' => $m->getEmail() ?: 'Maman #' . $m->getId(),
                'context' => '📞 ' . $m->getNumeroUrgence() . ' - Sanguin: ' . $m->getGroupeSanguin(),
                'date' => $m->getDateCreation(),
                'url' => $this->generateUrl('admin_maman_show', ['id' => $m->getId()]),
                'icon' => 'fa-user-nurse',
                'badge_text' => 'Maman',
                'badge_class' => 'bg-warning-light',
                'id' => $m->getId()
            ];
        }

        foreach ($recentGrossesses as $g) {
            $activityLists['grossesse'][] = [
                'type' => 'grossesse',
                'title' => 'Grossesse ' . ($g->getStatutGrossesse() === 'enCours' ? 'en cours' : $g->getStatutGrossesse()),
                'context' => 'Identifiée pour ' . ($g->getMaman() ? $g->getMaman()->getEmail() : 'maman inconnue'),
                'date' => $g->getDateCreation(),
                'url' => $this->generateUrl('admin_grosesse_show', ['id' => $g->getId()]),
                'icon' => 'fa-baby',
                'badge_text' => 'Grossesse',
                'badge_class' => 'bg-purple-light',
                'id' => $g->getId()
            ];
        }

        foreach ($recentConsultations as $c) {
            $activityLists['consultation'][] = [
                'type' => 'consultation',
                'title' => $c->getCategorie(),
                'context' => 'Pour: ' . $c->getPour(),
                'date' => $c->getCreatedAt(),
                'url' => $this->generateUrl('app_admin_consultation_show', ['id' => $c->getId()]),
                'icon' => 'fa-file-medical',
                'badge_text' => 'Consultation',
                'badge_class' => 'bg-secondary-light',
                'id' => $c->getId()
            ];
        }

        foreach ($recentCommandes as $co) {
            $activityLists['commande'][] = [
                'type' => 'commande',
                'title' => 'Commande #' . $co->getId(),
                'context' => $co->getStatut() . ' - Total: ' . $co->getTotal() . ' DT',
                'date' => $co->getDateCommande(),
                'url' => $this->generateUrl('app_commande_show', ['id' => $co->getId()]),
                'icon' => 'fa-shopping-cart',
                'badge_text' => 'Commande',
                'badge_class' => 'bg-indigo-light',
                'id' => $co->getId()
            ];
        }

        // Interleaving to ensure variety
        $activity = [];
        $maxItems = 0;
        foreach ($activityLists as $list) {
            $maxItems = max($maxItems, count($list));
        }

        for ($i = 0; $i < $maxItems; $i++) {
            foreach ($activityLists as $type => $list) {
                if (isset($list[$i])) {
                    $activity[] = $list[$i];
                    if (count($activity) >= 20)
                        break 2; // Limit to total 20 items (was 10, but now we have many types)
                }
            }
        }

        return $this->render('admin/dashboard.html.twig', [
            // Marketplace
            'nbProduits' => $nbProduits,
            'nbCommandesAttente' => $nbCommandesAttente,
            'nbCommandesValidees' => $nbCommandesValidees,
            'nbCommandesAnnulees' => $nbCommandesAnnulees,
            'chiffreAffaires' => $chiffreAffaires,
            'topProduits' => $topProduits,

            // Events
            'eventCount' => $eventCount,

            // Suivi grossesse
            'total_mamans' => $totalMamans,
            'total_grossesses' => $totalGrossesses,
            'suivis_grossesse_actifs' => $suivisActifs,

            // Existing dashboard data
            'creneaux_ce_mois' => $this->consultationCreneauRepository->countCeMois(),
            'recent_activity' => $activity,
        ]);
    }

    #[Route('/suivi-grossesse', name: 'suivi_grossesse', methods: ['GET'])]
    public function suiviGrossesse(
        MamanRepository $mamanRepository,
        GrosesseRepository $grosesseRepository
    ): Response {
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
