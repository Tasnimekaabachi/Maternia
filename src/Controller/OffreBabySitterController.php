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
    #[Route(name: 'app_offre_baby_sitter_index', methods: ['GET'])]
    public function index(OffreBabySitterRepository $offreBabySitterRepository): Response
    {
        return $this->render('offre_baby_sitter/index.html.twig', [
            'offre_baby_sitters' => $offreBabySitterRepository->findAll(),
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

            return $this->redirectToRoute('app_offre_baby_sitter_index', [], Response::HTTP_SEE_OTHER);
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

            return $this->redirectToRoute('app_offre_baby_sitter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offre_baby_sitter/edit.html.twig', [
            'offre_baby_sitter' => $offreBabySitter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offre_baby_sitter_delete', methods: ['POST'])]
    public function delete(Request $request, OffreBabySitter $offreBabySitter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offreBabySitter->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($offreBabySitter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_offre_baby_sitter_index', [], Response::HTTP_SEE_OTHER);
    }
}
