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
            // Tri principal par date d'ajout de la grossesse (dateCreation DESC)
            $grossesses = $grosesseRepository->findBy([], ['dateCreation' => 'DESC']);
            $statsStatut = $grosesseRepository->getStatsByStatut();

            return $this->render('admin/grossesse/index.html.twig', [
                'grossesses' => $grossesses,
                'stats_statut' => $statsStatut,
            ]);
        }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Grosesse $grosesse, ConseilsSuiviService $conseilsSuiviService): Response
    {
        $grossesseConseil = null;
        $bebeAgeMois = null;
        $bebeConseil = null;

        $semaine = $grosesse->getSemaineActuelle();
        if ($grosesse->getStatutGrossesse() !== 'terminee' && $semaine !== null) {
            $grossesseConseil = $conseilsSuiviService->conseilsGrossesse($semaine);
        } elseif ($grosesse->getStatutGrossesse() === 'terminee') {
            $bebeAgeMois = $conseilsSuiviService->getAgeBebeEnMois($grosesse);
            if ($bebeAgeMois !== null) {
                $bebeConseil = $conseilsSuiviService->conseilsBebe($bebeAgeMois);
            }
        }

        return $this->render('admin/grossesse/show.html.twig', [
            'grossesse' => $grosesse,
            'grossesse_conseil' => $grossesseConseil,
            'bebe_age_mois' => $bebeAgeMois,
            'bebe_conseil' => $bebeConseil,
        ]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(Grosesse $grosesse, ConseilsSuiviService $conseilsSuiviService): Response
    {
        $bebeAgeMois = $conseilsSuiviService->getAgeBebeEnMois($grosesse);

        return $this->render('admin/grossesse/pdf_print.html.twig', [
            'grossesse' => $grosesse,
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
            'form' => $form->createView(),
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

