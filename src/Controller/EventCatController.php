<?php

namespace App\Controller;

use App\Entity\EventCat;
use App\Form\EventCatType;
use App\Repository\EventCatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/event/cat')]
final class EventCatController extends AbstractController
{
    #[Route(name: 'app_event_cat_index', methods: ['GET'])]
    public function index(Request $request, EventCatRepository $eventCatRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $sortBy = $request->query->get('sort_by', 'name');
        $sortOrder = $request->query->get('sort_order', 'ASC');
        $event_cats = $eventCatRepository->findWithSearchAndSort($searchTerm, $sortBy, $sortOrder);

        return $this->render('event_cat/index.html.twig', [
            'event_cats' => $event_cats,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/pdf', name: 'app_event_cat_pdf', methods: ['GET'])]
    public function pdf(EventCatRepository $eventCatRepository): Response
    {
        $eventCats = $eventCatRepository->findAll();

        $html = $this->renderView('event_cat/pdf.html.twig', [
            'event_cats' => $eventCats,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="categories-maternia.pdf"',
            ]
        );
    }

    #[Route('/new', name: 'app_event_cat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $eventCat = new EventCat();
        $form = $this->createForm(EventCatType::class, $eventCat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($eventCat);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_cat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event_cat/new.html.twig', [
            'event_cat' => $eventCat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_cat_show', methods: ['GET'])]
    public function show(EventCat $eventCat): Response
    {
        return $this->render('event_cat/show.html.twig', [
            'event_cat' => $eventCat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_cat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EventCat $eventCat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventCatType::class, $eventCat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_cat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event_cat/edit.html.twig', [
            'event_cat' => $eventCat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_cat_delete', methods: ['POST'])]
    public function delete(Request $request, EventCat $eventCat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $eventCat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($eventCat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_cat_index', [], Response::HTTP_SEE_OTHER);
    }
}
