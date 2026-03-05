<?php

namespace App\Controller;

use App\Repository\ConsultationRepository;
use App\Repository\ConsultationCreneauRepository;
use App\Form\AppointmentType;
use App\Form\Model\AppointmentData;
use App\Repository\ProduitRepository;
use App\Repository\OffreBabySitterRepository;
use App\Service\WeatherApiService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private const MARKETPLACE_PER_PAGE = 8;

    public function __construct(
        private ConsultationRepository $consultationRepository,
        private ConsultationCreneauRepository $creneauRepository
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/marketplace', name: 'app_marketplace')]
    public function marketplace(Request $request, ProduitRepository $produitRepository, PaginatorInterface $paginator, WeatherApiService $weatherApiService): Response
    {
        $term = $request->query->get('q', '');
        $categorie = $request->query->get('categorie', '');
        $page = max(1, (int) $request->query->get('page', 1));

        $qb = $produitRepository->getQbByCategorieAndSearch(
            $categorie !== '' ? $categorie : null,
            $term
        );

        $pagination = $paginator->paginate($qb, $page, self::MARKETPLACE_PER_PAGE, [
            'distinct' => true,
        ]);

        $weather = $weatherApiService->getCurrentWeather();

        return $this->render('pages/marketplace.html.twig', [
            'produits' => $pagination,
            'pagination' => $pagination,
            'searchTerm' => $term,
            'categorieActive' => $categorie,
            'weather' => $weather,
        ]);
    }

    #[Route('/babysitting', name: 'app_babysitting')]
    public function babysitting(OffreBabySitterRepository $repository): Response
    {
        $offres = $repository->findAll();

        return $this->render('pages/babysitting.html.twig', [
            'offre_baby_sitters' => $offres,
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