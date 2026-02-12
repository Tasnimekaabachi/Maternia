<?php

namespace App\Controller\Admin;

use App\Entity\Maman;
use App\Form\MamanType;
use App\Repository\MamanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/maman', name: 'admin_maman_')]
final class MamanController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, MamanRepository $mamanRepository): Response
    {
        $groupeSanguin = $request->query->get('groupe_sanguin');
        $tri = $request->query->get('tri', 'date');
        $ordre = $request->query->get('ordre', 'DESC');

        $sortBy = $tri === 'taille'
            ? MamanRepository::SORT_TAILLE
            : ($tri === 'poids' ? MamanRepository::SORT_POIDS : MamanRepository::SORT_DATE);

        $mamans = $mamanRepository->findForAdmin($groupeSanguin, $sortBy, $ordre);
        $statsGroupe = $mamanRepository->getStatsByGroupeSanguin();
        $statsFumeur = $mamanRepository->getStatsByFumeur();

        return $this->render('admin/maman/index.html.twig', [
            'mamans' => $mamans,
            'stats_groupe_sanguin' => $statsGroupe,
            'stats_fumeur' => $statsFumeur,
            'filtre_groupe' => $groupeSanguin,
            'tri' => $tri,
            'ordre' => $ordre,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $maman = new Maman();
        $form = $this->createForm(MamanType::class, $maman);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($maman);
            $entityManager->flush();
            return $this->redirectToRoute('admin_suivi_grossesse', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/maman/new.html.twig', [
            'maman' => $maman,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Maman $maman): Response
    {
        return $this->render('admin/maman/show.html.twig', [
            'maman' => $maman,
        ]);
    }

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'])]
    public function pdf(Maman $maman): Response
    {
        return $this->render('admin/maman/pdf_print.html.twig', [
            'maman' => $maman,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Maman $maman, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MamanType::class, $maman);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_suivi_grossesse', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/maman/edit.html.twig', [
            'maman' => $maman,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Maman $maman, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $maman->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($maman);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_suivi_grossesse', [], Response::HTTP_SEE_OTHER);
    }
}
