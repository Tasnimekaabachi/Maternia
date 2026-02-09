<?php

namespace App\Controller;

use App\Entity\Maman;
use App\Form\MamanType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MamanController extends AbstractController
{
    /**
     * Page publique : la maman remplit son profil pour le suivi de grossesse.
     */
    #[Route('/mon-suivi-grossesse', name: 'app_suivi_grossesse_creer', methods: ['GET', 'POST'])]
    public function suiviGrossesseCreer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $maman = new Maman();
        $form = $this->createForm(MamanType::class, $maman);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($maman);
            $entityManager->flush();

            return $this->redirectToRoute('app_suivi_grossesse_show', ['id' => $maman->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/mon_profil_maman.html.twig', [
            'maman' => $maman,
            'form' => $form,
            'mode' => 'create',
        ]);
    }

    /**
     * Alias : /mon-suivi-grossesse/{id} redirige vers la vue personnelle.
     */
    #[Route('/mon-suivi-grossesse/{id}', name: 'app_suivi_grossesse_show_alias', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function suiviGrossesseShowAlias(Maman $maman): Response
    {
        return $this->redirectToRoute('app_suivi_grossesse_show', ['id' => $maman->getId()], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Vue personnelle : la maman consulte et gère ses infos (dashboard santé).
     * /suivi_grossesse/{id}
     */
    #[Route('/suivi_grossesse/{id}', name: 'app_suivi_grossesse_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function suiviGrossesseShow(Maman $maman): Response
    {
        $imc = $maman->getImc();
        $imcCategorie = $maman->getImcCategorie();
        $imcAlerte = $maman->isImcAlerte();
        $conseils = $this->getConseilsSante($maman);

        return $this->render('pages/mon_profil_maman.html.twig', [
            'maman' => $maman,
            'mode' => 'show',
            'imc' => $imc,
            'imc_categorie' => $imcCategorie,
            'imc_alerte' => $imcAlerte,
            'conseils' => $conseils,
        ]);
    }

    /**
     * Édition du profil par la maman. /suivi_grossesse/{id}/edit
     */
    #[Route('/suivi_grossesse/{id}/edit', name: 'app_suivi_grossesse_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function suiviGrossesseEdit(Request $request, Maman $maman, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MamanType::class, $maman);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_suivi_grossesse_show', ['id' => $maman->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/mon_profil_maman.html.twig', [
            'maman' => $maman,
            'form' => $form,
            'mode' => 'edit',
        ]);
    }

    /**
     * Suppression du profil par la maman. /suivi_grossesse/{id}/supprimer
     */
    #[Route('/suivi_grossesse/{id}/supprimer', name: 'app_suivi_grossesse_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function suiviGrossesseDelete(Request $request, Maman $maman, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $maman->getId(), $request->request->getString('_token'))) {
            $entityManager->remove($maman);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_suivi_grossesse_creer', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Conseils santé personnalisés selon IMC et mode de vie.
     *
     * @return string[]
     */
    private function getConseilsSante(Maman $maman): array
    {
        $conseils = [];
        $imc = $maman->getImc();
        $categorie = $maman->getImcCategorie();

        if ($imc !== null) {
            if ($imc < 18.5) {
                $conseils[] = 'Votre IMC indique une maigreur. Pensez à des repas équilibrés et à en parler à votre sage-femme ou médecin.';
            } elseif ($imc >= 30) {
                $conseils[] = 'Un suivi nutritionnel peut vous aider à gérer votre poids pendant la grossesse en toute sécurité.';
            }
            if ($imc >= 18.5 && $imc < 25) {
                $conseils[] = 'Votre IMC est dans la norme. Continuez une alimentation variée et une activité physique adaptée.';
            }
        }

        if ($maman->isFumeur()) {
            $conseils[] = 'Arrêter de fumer est l’un des meilleurs gestes pour vous et bébé. N’hésitez pas à demander de l’aide (substituts, accompagnement).';
        }
        if ($maman->isConsommationAlcool()) {
            $conseils[] = 'Zéro alcool pendant la grossesse est recommandé. Nous pouvons vous orienter vers un accompagnement si besoin.';
        }

        $niveau = $maman->getNiveauActivitePhysique();
        if ($niveau === 'Sédentaire' || $niveau === 'Léger') {
            $conseils[] = 'Marcher 20–30 minutes par jour est bénéfique pendant la grossesse. Adaptez l’intensité à votre forme.';
        }

        if (empty($conseils)) {
            $conseils[] = 'Continuez à prendre soin de vous : alimentation équilibrée, repos et suivi régulier.';
        }

        return $conseils;
    }
}
