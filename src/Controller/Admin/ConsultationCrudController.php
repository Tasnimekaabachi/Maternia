<?php

namespace App\Controller\Admin;

use App\Entity\Consultation;
use App\Form\ConsultationType;
use App\Repository\ConsultationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/consultations')]
class ConsultationCrudController extends AbstractController
{
    #[Route('/', name: 'app_admin_consultation_index', methods: ['GET'])]
    public function index(ConsultationRepository $consultationRepository): Response
    {
        return $this->render('admin/consultation_crud/index.html.twig', [
            'consultations' => $consultationRepository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'app_admin_consultation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consultation = new Consultation();
        // CORRECTION ICI : Utilisez DateTime au lieu de DateTimeImmutable
        $consultation->setCreatedAt(new \DateTime());
        $consultation->setUpdatedAt(new \DateTime());
        
        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($consultation);
            $entityManager->flush();

            $this->addFlash('success', 'Consultation créée avec succès.');
            return $this->redirectToRoute('app_admin_consultation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/consultation_crud/new.html.twig', [
            'consultation' => $consultation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_consultation_show', methods: ['GET'])]
    public function show(Consultation $consultation): Response
    {
        return $this->render('admin/consultation_crud/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_consultation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        // CORRECTION ICI : Utilisez DateTime au lieu de DateTimeImmutable
        $consultation->setUpdatedAt(new \DateTime());
        
        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Consultation modifiée avec succès.');
            return $this->redirectToRoute('app_admin_consultation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/consultation_crud/edit.html.twig', [
            'consultation' => $consultation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_consultation_delete', methods: ['POST'])]
    public function delete(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$consultation->getId(), $request->request->get('_token'))) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Token CSRF invalide.'], 400);
            }
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_consultation_index');
        }

        try {
            $entityManager->remove($consultation);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => true, 'message' => 'Consultation supprimée avec succès.']);
            }
            $this->addFlash('success', 'Consultation supprimée avec succès.');
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
            }
            $this->addFlash('error', 'Erreur lors de la suppression.');
        }

        return $this->redirectToRoute('app_admin_consultation_index', [], Response::HTTP_SEE_OTHER);
    }
}