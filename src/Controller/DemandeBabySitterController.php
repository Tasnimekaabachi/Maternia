<?php

namespace App\Controller;

use App\Entity\DemandeBabySitter;
use App\Form\DemandeBabySitterType;
use App\Repository\DemandeBabySitterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demande/baby/sitter')]
final class DemandeBabySitterController extends AbstractController
{
    #[Route(name: 'app_demande_baby_sitter_index', methods: ['GET'])]
    public function index(DemandeBabySitterRepository $demandeBabySitterRepository): Response
    {
        return $this->render('demande_baby_sitter/index.html.twig', [
            'demande_baby_sitters' => $demandeBabySitterRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_demande_baby_sitter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $demandeBabySitter = new DemandeBabySitter();
        $form = $this->createForm(DemandeBabySitterType::class, $demandeBabySitter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($demandeBabySitter);
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_baby_sitter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande_baby_sitter/new.html.twig', [
            'demande_baby_sitter' => $demandeBabySitter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_baby_sitter_show', methods: ['GET'])]
    public function show(DemandeBabySitter $demandeBabySitter): Response
    {
        return $this->render('demande_baby_sitter/show.html.twig', [
            'demande_baby_sitter' => $demandeBabySitter,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_demande_baby_sitter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DemandeBabySitter $demandeBabySitter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeBabySitterType::class, $demandeBabySitter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_baby_sitter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande_baby_sitter/edit.html.twig', [
            'demande_baby_sitter' => $demandeBabySitter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_baby_sitter_delete', methods: ['POST'])]
    public function delete(Request $request, DemandeBabySitter $demandeBabySitter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demandeBabySitter->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demandeBabySitter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demande_baby_sitter_index', [], Response::HTTP_SEE_OTHER);
    }
}
