<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Finder\Finder;
use Dompdf\Dompdf;
use Dompdf\Options;

class EventController extends AbstractController
{
    #[Route('/admin/event/pdf', name: 'app_event_pdf', methods: ['GET'])]
    public function pdf(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        $html = $this->renderView('admin/event/pdf.html.twig', [
            'events' => $events,
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
                'Content-Disposition' => 'attachment; filename="events-maternia.pdf"',
            ]
        );
    }

    #[Route('/admin/event/', name: 'app_event_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $sortBy = $request->query->get('sort_by', 'startAt');
        $sortOrder = $request->query->get('sort_order', 'DESC');
        $events = $eventRepository->findWithSearchAndSort($searchTerm, $sortBy, $sortOrder);

        return $this->render('admin/event/index.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/admin/event/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        // Get existing images from img directory for the gallery
        $existingImages = $this->getExistingImages();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image selection
            $selectedImage = $form->get('selectedImage')->getData();

            if ($selectedImage) {
                // Use selected existing image
                $event->setImage($selectedImage);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès.');
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingImages' => $existingImages,
        ]);
    }

    #[Route('/event/new', name: 'app_event_user_new', methods: ['GET', 'POST'])]
    public function new1(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        // Get existing images from img directory for the gallery
        $existingImages = $this->getExistingImages();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image selection
            $selectedImage = $form->get('selectedImage')->getData();

            if ($selectedImage) {
                // Use selected existing image
                $event->setImage($selectedImage);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Votre événement a été créé avec succès.');
            return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/usernew.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingImages' => $existingImages,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_user_show', methods: ['GET'])]
    public function show1(Event $event): Response
    {
        return $this->render('event/usershow.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/admin/event/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/admin/event/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        // Store current image for comparison
        $currentImage = $event->getImage();

        $form = $this->createForm(EventType::class, $event);

        // Get existing images from img directory for the gallery
        $existingImages = $this->getExistingImages();

        // Set initial value for selectedImage if event has an image
        if ($currentImage) {
            $form->get('selectedImage')->setData($currentImage);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image selection
            $selectedImage = $form->get('selectedImage')->getData();

            if ($selectedImage && $selectedImage !== $currentImage) {
                // Use selected existing image (different from current)
                $event->setImage($selectedImage);
            }
            // If no image is selected, keep the current one

            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été modifié avec succès.');
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingImages' => $existingImages,
            'currentImage' => $currentImage,
        ]);
    }

    #[Route('/admin/event/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    public function publicEvents(EventRepository $eventRepository, ?int $limit = null): Response
    {
        $events = $eventRepository->findUpcomingEvents($limit);

        return $this->render('event/_public_list.html.twig', [
            'events' => $events,
            'limit' => $limit
        ]);
    }

    /**
     * Get existing images from the img directory for gallery
     */
    private function getExistingImages(): array
    {
        $images = [];
        $imgDir = $this->getParameter('kernel.project_dir') . '/public/img/events/';

        if (file_exists($imgDir)) {
            $finder = new Finder();
            $finder->files()->in($imgDir)->name(['*.jpg', '*.jpeg', '*.png', '*.gif', '*.webp']);

            foreach ($finder as $file) {
                $images[] = $file->getFilename();
            }

            // Sort alphabetically
            sort($images);
        }

        return $images;
    }
}