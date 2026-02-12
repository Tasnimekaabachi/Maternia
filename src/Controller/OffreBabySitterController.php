<?php

namespace App\Controller;

use App\Entity\OffreBabySitter;
use App\Form\OffreBabySitterType;
use App\Repository\OffreBabySitterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offre/baby/sitter')]
final class OffreBabySitterController extends AbstractController
{
    /**
     * Liste des offres Babysitter en BackOffice.
     *
     * Template utilisé : templates/admin/profil_bebe.html.twig
     */
    #[Route(name: 'app_offre_baby_sitter_index', methods: ['GET'])]
    public function index(Request $request, OffreBabySitterRepository $repository): Response
    {
        $ville = $request->query->get('ville');
        $tarifParam = $request->query->get('tarif');
        $sort = $request->query->get('sort');

        // Conversion sécurisée du tarif en float (ou null si vide)
        $tarif = null;
        if ($tarifParam !== null && $tarifParam !== '') {
            $tarif = (float) $tarifParam;
        }

        // Recherche avancée (ville, tarif max, tri)
        $offres = $repository->search($ville, $tarif, $sort);
        $statsVille = $repository->statsByVille();

        return $this->render('admin/profil_bebe.html.twig', [
            'offre_baby_sitters' => $offres,
            'ville' => $ville,
            'tarif' => $tarifParam,
            'sort' => $sort,
            'stats_ville' => $statsVille,
        ]);
    }
    #[Route('/new', name: 'app_offre_baby_sitter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offreBabySitter = new OffreBabySitter();
        $form = $this->createForm(OffreBabySitterType::class, $offreBabySitter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offreBabySitter);
            $entityManager->flush();

            // Après ajout, retour vers le BackOffice profil bébé
            return $this->redirectToRoute('admin_profil_bebe');
        }

        return $this->render('offre_baby_sitter/new.html.twig', [
            'offre_baby_sitter' => $offreBabySitter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offre_baby_sitter_show', methods: ['GET'])]
    public function show(OffreBabySitter $offreBabySitter): Response
    {
        return $this->render('offre_baby_sitter/show.html.twig', [
            'offre_baby_sitter' => $offreBabySitter,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offre_baby_sitter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OffreBabySitter $offreBabySitter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreBabySitterType::class, $offreBabySitter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Après modification, retour vers le BackOffice profil bébé
            return $this->redirectToRoute('admin_profil_bebe');
        }

        return $this->render('offre_baby_sitter/edit.html.twig', [
            'offre_baby_sitter' => $offreBabySitter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offre_baby_sitter_delete', methods: ['POST'])]
    public function delete(Request $request, OffreBabySitter $offreBabySitter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offreBabySitter->getId(), $request->request->get('_token'))) {
            $entityManager->remove($offreBabySitter);
            $entityManager->flush();
        }

        // Après suppression, retour vers le BackOffice profil bébé
        return $this->redirectToRoute('admin_profil_bebe');
    }
}
