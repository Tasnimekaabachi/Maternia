<?php

namespace App\Controller;

use App\Entity\Grosesse;
use App\Entity\Maman;
use App\Form\GrosesseType;
use App\Repository\GrosesseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Étape 2 : formulaire grossesse côté maman (suite du profil).
 *
 * URL : /suivi_grossesse/{id}/grossesse
 * - {id} = id de la maman
 * - si aucune grossesse : création
 * - si grossesse existe : édition
 */
#[Route('/suivi_grossesse/{id}/grossesse', name: 'app_maman_grossesse_', requirements: ['id' => '\d+'])]
final class MamanGrossesseController extends AbstractController
{
    #[Route('', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Maman $maman,
        Request $request,
        GrosesseRepository $grosesseRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Une seule grossesse liée à cette maman pour le front (simplification)
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
            if ($isNew) {
                $entityManager->persist($grossesse);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations de grossesse ont été enregistrées.');

            // Retour à l'étape 1 (profil maman)
            return $this->redirectToRoute('app_suivi_grossesse_show', ['id' => $maman->getId()]);
        }

        return $this->render('maman/grossesse.html.twig', [
            'maman' => $maman,
            'grossesse' => $grossesse,
            'form' => $form->createView(),
            'is_new' => $isNew,
        ]);
    }
}

