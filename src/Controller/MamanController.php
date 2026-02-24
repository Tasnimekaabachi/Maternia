<?php

namespace App\Controller;

use App\Entity\Maman;
use App\Form\MamanType;
use App\Repository\GrosesseRepository;
use App\Service\ConseilsSuiviService;
use App\Service\MailerService;
use App\Service\ChatbotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
final class MamanController extends AbstractController
{
    /**
     * Page publique : la maman remplit son profil pour le suivi de grossesse.
     * URL unifiée : /suivi_grossesse (création) et /suivi_grossesse/{id} (voir une maman).
     */
    #[Route('/suivi_grossesse', name: 'app_suivi_grossesse_creer', methods: ['GET', 'POST'])]
    public function suiviGrossesseCreer(Request $request, EntityManagerInterface $entityManager, MailerService $mailerService): Response
    {
        $maman = new Maman();
        $form = $this->createForm(MamanType::class, $maman);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($maman);
            $entityManager->flush();

            // Envoi de l'email de bienvenue si une adresse est fournie
            $emailSent = $mailerService->sendWelcomeEmail($maman);
            if ($emailSent) {
                $this->addFlash('success', 'Votre profil a été créé et un email de confirmation a été envoyé.');
            } else {
                $this->addFlash('success', 'Votre profil a été créé. Ajoutez une adresse email valide pour recevoir une confirmation.');
            }

            // Étape 2 : enchaîner directement sur le formulaire grossesse
            return $this->redirectToRoute('app_maman_grossesse_edit', ['id' => $maman->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/mon_profil_maman.html.twig', [
            'maman' => $maman,
            'form' => $form,
            'mode' => 'create',
        ]);
    }

    /**
     * Ancienne URL : GET /mon-suivi-grossesse redirige vers /suivi_grossesse.
     */
    #[Route('/mon-suivi-grossesse', name: 'app_suivi_grossesse_creer_alias', methods: ['GET'])]
    public function suiviGrossesseCreerAlias(Request $request): Response
    {
        return $this->redirectToRoute('app_suivi_grossesse_creer', $request->query->all(), Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Ancienne URL : /mon-suivi-grossesse/{id} redirige vers /suivi_grossesse/{id}.
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
public function suiviGrossesseShow(Maman $maman, GrosesseRepository $grosesseRepository, ConseilsSuiviService $conseilsSuiviService): Response    {
        $imc = $maman->getImc();
        $imcCategorie = $maman->getImcCategorie();
        $imcAlerte = $maman->isImcAlerte();

        $conseils = $this->getConseilsSante($maman);

        $grossesse = $grosesseRepository->findOneBy(['maman' => $maman], ['dateCreation' => 'DESC']);

        $grossesseConseil = null;
        $grossesseAlertes = [];
        $bebeAgeMois = null;
        $bebeConseil = null;

        if ($grossesse) {
            $semaine = $grossesse->getSemaineActuelle();
            $statut = $grossesse->getStatutGrossesse();

            if ($statut !== 'terminee' && $semaine !== null) {
                $grossesseConseil = $conseilsSuiviService->conseilsGrossesse($semaine);

                if ($statut === 'aRisque') {
                    $grossesseAlertes[] = 'Grossesse déclarée à risque : un suivi rapproché avec votre médecin est recommandé.';
                }
                if ($grossesse->getTypeGrossesse() === 'multiple') {
                    $grossesseAlertes[] = 'Grossesse multiple : contrôles prénataux plus fréquents conseillés.';
                }
                if ($imcAlerte) {
                    $grossesseAlertes[] = 'IMC à risque : surveillez la prise de poids avec un professionnel de santé.';
                }
                // Graphique prise de poids
$poidsAvant  = $maman->getPoids();
$poidsActuel = $grossesse->getPoidsActuel();
$prisePoids  = ($poidsAvant && $poidsActuel)
               ? round($poidsActuel - $poidsAvant, 1)
               : null;

$evaluationPrise = null;
if ($prisePoids !== null) {
    if ($prisePoids < 0)       $evaluationPrise = 'perte';
    elseif ($prisePoids < 8)   $evaluationPrise = 'insuffisant';
    elseif ($prisePoids <= 16) $evaluationPrise = 'normal';
    elseif ($prisePoids <= 20) $evaluationPrise = 'attention';
    else                       $evaluationPrise = 'excessif';
}
           } elseif ($statut === 'terminee') {
    $bebeAgeMois = $conseilsSuiviService->getAgeBebeEnMois($grossesse);
    if ($bebeAgeMois !== null) {
        $bebeConseil = $conseilsSuiviService->conseilsBebe($bebeAgeMois);
    }

    // Données courbe bébé
    $sexe = $grossesse->getSexeBebe(); // 'M' ou 'F'

    // Normes OMS à la naissance
    $normes = [
        'M' => ['poids_min' => 2.9, 'poids_max' => 4.0, 'taille_min' => 48.0, 'taille_max' => 52.0],
        'F' => ['poids_min' => 2.8, 'poids_max' => 3.8, 'taille_min' => 47.0, 'taille_max' => 51.0],
    ];

    $normesBebe = $normes[$sexe] ?? $normes['M'];
    $poidsBebe  = $grossesse->getPoidsNaissance();
    $tailleBebe = $grossesse->getTailleNaissance();

    // Évaluation poids
    $evaluationPoids = 'normal';
    if ($poidsBebe !== null) {
        if ($poidsBebe < $normesBebe['poids_min']) $evaluationPoids = 'faible';
        elseif ($poidsBebe > $normesBebe['poids_max']) $evaluationPoids = 'eleve';
    }

    // Évaluation taille
$evaluationTaille = 'normal';
    if ($tailleBebe !== null) {
        if ($tailleBebe < $normesBebe['taille_min']) $evaluationTaille = 'faible';
        elseif ($tailleBebe > $normesBebe['taille_max']) $evaluationTaille = 'eleve';
    }
        }
        }
        return $this->render('pages/mon_profil_maman.html.twig', [
            'maman' => $maman,
            'mode' => 'show',
            'imc' => $imc,
            'imc_categorie' => $imcCategorie,
            'imc_alerte' => $imcAlerte,
            'conseils' => $conseils,
            'grossesse' => $grossesse,
            'grossesse_conseil' => $grossesseConseil,
            'grossesse_alertes' => $grossesseAlertes,
            'bebe_age_mois' => $bebeAgeMois,
            'bebe_conseil' => $bebeConseil,
            'normes_bebe'       => $normesBebe ?? null,
'evaluation_poids'  => $evaluationPoids ?? null,
'evaluation_taille' => $evaluationTaille ?? null,
'poids_avant'      => $maman->getPoids() ?? null,
'poids_actuel_g'   => $grossesse ? $grossesse->getPoidsActuel() ?? null : null,
'prise_poids'      => $prisePoids ?? null,
'evaluation_prise' => $evaluationPrise ?? null,
'gemini_api_key' => $_ENV['GEMINI_API_KEY'] ?? '',
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
    #[Route('/suivi_grossesse/{id}/chatbot', name: 'app_chatbot_ask', methods: ['POST'])]
public function chatbotAsk(Request $request, Maman $maman, ChatbotService $chatbotService): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $question = $data['question'] ?? '';

    if (empty($question)) {
        return new JsonResponse(['error' => 'Question vide'], 400);
    }

    $context = [
        'groupeSanguin' => $maman->getGroupeSanguin(),
        'maladies'      => $maman->getMaladiesChroniques(),
        'allergies'     => $maman->getAllergies(),
    ];

    $reponse = $chatbotService->ask($question, $context);

    return new JsonResponse(['reponse' => $reponse]);
}
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
