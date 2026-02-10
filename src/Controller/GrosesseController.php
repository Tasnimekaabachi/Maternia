<?php

namespace App\Controller;

use App\Entity\Grosesse;
use App\Form\GrosesseType;
use App\Repository\GrosesseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/grosesse')]
final class GrosesseController extends AbstractController
{
    #[Route(name: 'app_grosesse_index', methods: ['GET'])]
    public function index(GrosesseRepository $grosesseRepository): Response
    {
        return $this->render('grosesse/index.html.twig', [
            'grosesses' => $grosesseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_grosesse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $grosesse = new Grosesse();
        $form = $this->createForm(GrosesseType::class, $grosesse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($grosesse);
            $entityManager->flush();

            return $this->redirectToRoute('app_grosesse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grosesse/new.html.twig', [
            'grosesse' => $grosesse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grosesse_show', methods: ['GET'])]
    public function show(Grosesse $grosesse): Response
    {
        return $this->render('grosesse/show.html.twig', [
            'grosesse' => $grosesse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_grosesse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Grosesse $grosesse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GrossesseType::class, $grosesse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_grosesse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grosesse/edit.html.twig', [
            'grosesse' => $grosesse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grosesse_delete', methods: ['POST'])]
    public function delete(Request $request, Grossesse $grosesse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$grosesse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($grosesse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_grosesse_index', [], Response::HTTP_SEE_OTHER);
    }
}
