<?php

namespace App\Controller;

use App\Form\AppointmentType;
use App\Form\Model\AppointmentData;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/marketplace', name: 'app_marketplace')]
    public function marketplace(Request $request, ProduitRepository $produitRepository): Response
    {
        $term = $request->query->get('q', '');

        if ($term) {
            $produits = $produitRepository->search($term);
        } else {
            $produits = $produitRepository->findAll();
        }

        return $this->render('pages/marketplace.html.twig', [
            'produits' => $produits,
            'searchTerm' => $term,
        ]);
    }

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('pages/services.html.twig');
    }

    #[Route('/rendez-vous', name: 'app_appointment', methods: ['GET', 'POST'])]
    public function appointment(Request $request): Response
    {
        $data = new AppointmentData();
        $form = $this->createForm(AppointmentType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ici, on pourrait envoyer un email ou enregistrer la demande.
            $this->addFlash('success', 'Votre demande de rendez-vous a été envoyée. Nous vous contacterons pour confirmer.');

            return $this->redirectToRoute('app_appointment');
        }

        return $this->render('pages/appointment.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/evenements', name: 'app_events')]
    public function events(): Response
    {
        return $this->render('pages/events.html.twig');
    }
}
