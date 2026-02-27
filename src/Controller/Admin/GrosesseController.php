<?php

namespace App\Controller\Admin;

use App\Entity\Grosesse;
use App\Form\GrosesseType;
use App\Repository\GrosesseRepository;
use App\Service\ConseilsSuiviService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/grossesse', name: 'admin_grosesse_')]
final class GrosesseController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(GrosesseRepository $grosesseRepository): Response
    {
        $grossesses  = $grosesseRepository->findForAdminSorted();
        $statsStatut = $grosesseRepository->getStatsByStatut();

        return $this->render('admin/grossesse/index.html.twig', [
            'grossesses'   => $grossesses,
            'stats_statut' => $statsStatut,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Grosesse $grosesse, ConseilsSuiviService $conseilsSuiviService): Response
    {
        $grossesseConseil = null;
        $bebeAgeMois      = null;
        $bebeConseil      = null;
        $normesBebe       = null;
        $evaluationPoids  = null;
        $evaluationTaille = null;
        $poidsAvant       = null;
        $poidsActuelG     = null;
        $prisePoids       = null;
        $evaluationPrise  = null;
        $alerteSemaine    = null; 

        $semaine = $grosesse->getSemaineActuelle();
        $statut  = $grosesse->getStatutGrossesse();
        $maman   = $grosesse->getMaman();

        if ($statut !== 'terminee' && $semaine !== null) {
            $grossesseConseil = $conseilsSuiviService->conseilsGrossesse($semaine);

            // Graphique prise de poids
            $poidsAvant   = $maman?->getPoids();
            $poidsActuelG = $grosesse->getPoidsActuel();
            $prisePoids   = ($poidsAvant && $poidsActuelG)
                ? round($poidsActuelG - $poidsAvant, 1)
                : null;

            if ($prisePoids !== null) {
                if ($prisePoids < 0)       $evaluationPrise = 'perte';
                elseif ($prisePoids < 8)   $evaluationPrise = 'insuffisant';
                elseif ($prisePoids <= 16) $evaluationPrise = 'normal';
                elseif ($prisePoids <= 20) $evaluationPrise = 'attention';
                else                       $evaluationPrise = 'excessif';
            }

            //Alerte selon semaine
            if ($semaine >= 40) {
                $alerteSemaine = [
                    'niveau'  => 'danger',
                    'message' => '🚨 Terme dépassé — suivi urgent recommandé',
                ];
            } elseif ($semaine >= 36) {
                $alerteSemaine = [
                    'niveau'  => 'warning',
                    'message' => '⚠️ Grossesse proche du terme — préparez le suivi final',
                ];
            } 

        } elseif ($statut === 'terminee') {
            $bebeAgeMois = $conseilsSuiviService->getAgeBebeEnMois($grosesse);
            if ($bebeAgeMois !== null) {
                $bebeConseil = $conseilsSuiviService->conseilsBebe($bebeAgeMois);
            }

            $sexe   = $grosesse->getSexeBebe();
            $normes = [
                'M' => ['poids_min' => 2.9, 'poids_max' => 4.0, 'taille_min' => 48.0, 'taille_max' => 52.0],
                'F' => ['poids_min' => 2.8, 'poids_max' => 3.8, 'taille_min' => 47.0, 'taille_max' => 51.0],
            ];
            $normesBebe = $normes[$sexe] ?? $normes['M'];

            $poidsBebe  = $grosesse->getPoidsNaissance();
            $tailleBebe = $grosesse->getTailleNaissance();

            $evaluationPoids = 'normal';
            if ($poidsBebe !== null) {
                if ($poidsBebe < $normesBebe['poids_min'])     $evaluationPoids = 'faible';
                elseif ($poidsBebe > $normesBebe['poids_max']) $evaluationPoids = 'eleve';
            }

            $evaluationTaille = 'normal';
            if ($tailleBebe !== null) {
                if ($tailleBebe < $normesBebe['taille_min'])     $evaluationTaille = 'faible';
                elseif ($tailleBebe > $normesBebe['taille_max']) $evaluationTaille = 'eleve';
            }
        }

        return $this->render('admin/grossesse/show.html.twig', [
            'grossesse'         => $grosesse,
            'grossesse_conseil' => $grossesseConseil,
            'bebe_age_mois'     => $bebeAgeMois,
            'bebe_conseil'      => $bebeConseil,
            'normes_bebe'       => $normesBebe,
            'evaluation_poids'  => $evaluationPoids,
            'evaluation_taille' => $evaluationTaille,
            'poids_avant'       => $poidsAvant,
            'poids_actuel_g'    => $poidsActuelG,
            'prise_poids'       => $prisePoids,
            'evaluation_prise'  => $evaluationPrise,
            'alerte_semaine'    => $alerteSemaine, 
        ]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(Grosesse $grosesse, ConseilsSuiviService $conseilsSuiviService): Response
    {
        $bebeAgeMois = $conseilsSuiviService->getAgeBebeEnMois($grosesse);

        return $this->render('admin/grossesse/pdf_print.html.twig', [
            'grossesse'     => $grosesse,
            'bebe_age_mois' => $bebeAgeMois,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Grosesse $grosesse, GrosesseRepository $grosesseRepository): Response
    {
        $form = $this->createForm(GrosesseType::class, $grosesse, [
            'include_maman' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $grosesseRepository->save($grosesse, true);
            $this->addFlash('success', 'Grossesse mise à jour avec succès.');
            return $this->redirectToRoute('admin_grosesse_index');
        }

        return $this->render('admin/grossesse/edit.html.twig', [
            'grossesse' => $grosesse,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Grosesse $grosesse, GrosesseRepository $grosesseRepository): Response
    {
        $grosesseRepository->remove($grosesse, true);
        $this->addFlash('success', 'Grossesse supprimée avec succès.');
        return $this->redirectToRoute('admin_grosesse_index');
    }
}