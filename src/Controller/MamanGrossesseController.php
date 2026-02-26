<?php
namespace App\Controller;

use App\Entity\Grosesse;
use App\Entity\Maman;
use App\Form\GrosesseType;
use App\Repository\GrosesseRepository;
use App\Service\RiskPredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/suivi_grossesse/{id}/grossesse', name: 'app_maman_grossesse_', requirements: ['id' => '\d+'])]
final class MamanGrossesseController extends AbstractController
{
    public function __construct(private RiskPredictionService $riskService) {}

    #[Route('', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Maman $maman,
        Request $request,
        GrosesseRepository $grosesseRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $grossesse = $grosesseRepository->findOneBy(['maman' => $maman]);
        $isNew = false;

        if (!$grossesse) {
            $grossesse = new Grosesse();
            $grossesse->setMaman($maman);
            $isNew = true;
        }

        $form = $this->createForm(GrosesseType::class, $grossesse, [
            'include_maman' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Appel ML
            $symptomes = [
                'nausee'             => $grossesse->isNausee() ? 1 : 0,
                'vomissement'        => $grossesse->isVomissement() ? 1 : 0,
                'saignement'         => $grossesse->isSaignement() ? 1 : 0,
                'fievre'             => $grossesse->isFievre() ? 1 : 0,
                'douleur_abdominale' => $grossesse->isDouleurAbdominale() ? 1 : 0,
                'fatigue'            => $grossesse->isFatigue() ? 1 : 0,
                'vertiges'           => $grossesse->isVertiges() ? 1 : 0,
            ];

            $result = $this->riskService->predict($symptomes);
            $grossesse->setRiskLevel($result['risk']);

            if ($isNew) {
                $entityManager->persist($grossesse);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations de grossesse ont été enregistrées.');
            return $this->redirectToRoute('app_suivi_grossesse_show', ['id' => $maman->getId()]);
        }

        return $this->render('maman/grossesse.html.twig', [
            'maman'    => $maman,
            'grossesse' => $grossesse,
            'form'     => $form->createView(),
            'is_new'   => $isNew,
        ]);
    }
}