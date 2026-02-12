<?php

namespace App\Controller\Admin;

use App\Repository\ConsultationCreneauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EventRepository;

#[Route('/admin', name: 'admin_')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ConsultationCreneauRepository $consultationCreneauRepository
    ) {
    }

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(EventRepository $eventRepository): Response
    {
        $eventCount = $eventRepository->count([]);

        // Fetch recent items
        $recentCreneaux = $this->consultationCreneauRepository->findBy([], ['id' => 'DESC'], 10);
        $recentEvents = $eventRepository->findBy([], ['id' => 'DESC'], 10);

        $activity = [];

        foreach ($recentCreneaux as $c) {
            $activity[] = [
                'type' => 'creneau',
                'title' => $c->getNomMedecin(),
                'context' => $c->getSpecialiteMedecin() ?: $c->getConsultation()->getCategorie(),
                'date' => $c->getDateDebut(),
                'url' => $this->generateUrl('app_admin_consultation_creneau_show', ['id' => $c->getId()]),
                'icon' => 'fa-stethoscope',
                'badge_text' => 'Créneau',
                'badge_class' => 'bg-info-light',
                'id' => $c->getId() // Used for sorting
            ];
        }

        foreach ($recentEvents as $e) {
            $activity[] = [
                'type' => 'event',
                'title' => $e->getTitle(),
                'context' => $e->getEventCat()->getName(),
                'date' => $e->getStartAt(),
                'url' => $this->generateUrl('app_event_show', ['id' => $e->getId()]),
                'icon' => 'fa-calendar-alt',
                'badge_text' => 'Événement',
                'badge_class' => 'bg-pink-light',
                'id' => $e->getId() // Used for sorting
            ];
        }

        // Sort by ID DESC (most recently added first)
        // Note: For a real app, adding a createdAt column to both would be better
        usort($activity, fn($a, $b) => $b['id'] <=> $a['id']);

        // Final result limited to 10
        $activity = array_slice($activity, 0, 10);

        return $this->render('admin/dashboard.html.twig', [
            'eventCount' => $eventCount,
            'creneaux_ce_mois' => $this->consultationCreneauRepository->countCeMois(),
            'recent_activity' => $activity,
        ]);
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