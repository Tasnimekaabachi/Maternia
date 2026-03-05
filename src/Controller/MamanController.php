<?php

namespace App\Controller;

use App\Entity\Maman;
use App\Form\MamanType;
use App\Repository\GrosesseRepository;
use App\Service\BebeSemaineService;
use App\Service\ConseilsSuiviService;
use App\Service\MailerService;
use App\Service\ChatbotService;
use App\Service\NutritionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class MamanController extends AbstractController
{
    #[Route('/suivi_grossesse', name: 'app_suivi_grossesse_creer', methods: ['GET', 'POST'])]
    public function suiviGrossesseCreer(Request $request, EntityManagerInterface $entityManager, MailerService $mailerService): Response
    {
        $maman = new Maman();
        $form  = $this->createForm(MamanType::class, $maman);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($maman);
            $entityManager->flush();

            $emailSent = $mailerService->sendWelcomeEmail($maman);
            if ($emailSent) {
                $this->addFlash('success', 'Votre profil a été créé et un email de confirmation a été envoyé.');
            } else {
                $this->addFlash('success', 'Votre profil a été créé. Ajoutez une adresse email valide pour recevoir une confirmation.');
            }

            return $this->redirectToRoute('app_maman_grossesse_edit', ['id' => $maman->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/mon_profil_maman.html.twig', [
            'maman' => $maman,
            'form'  => $form,
            'mode'  => 'create',
        ]);
    }

    #[Route('/mon-suivi-grossesse', name: 'app_suivi_grossesse_creer_alias', methods: ['GET'])]
    public function suiviGrossesseCreerAlias(Request $request): Response
    {
        return $this->redirectToRoute('app_suivi_grossesse_creer', $request->query->all(), Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/mon-suivi-grossesse/{id}', name: 'app_suivi_grossesse_show_alias', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function suiviGrossesseShowAlias(Maman $maman): Response
    {
        return $this->redirectToRoute('app_suivi_grossesse_show', ['id' => $maman->getId()], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/suivi_grossesse/{id}', name: 'app_suivi_grossesse_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function suiviGrossesseShow(
        Maman $maman,
        GrosesseRepository $grosesseRepository,
        ConseilsSuiviService $conseilsSuiviService,
        NutritionService $nutritionService,
        BebeSemaineService $bebeSemaineService
    ): Response {
        $imc          = $maman->getImc();
        $imcCategorie = $maman->getImcCategorie();
        $imcAlerte    = $maman->isImcAlerte();
        $conseils     = $this->getConseilsSante($maman);

        $grossesse        = $grosesseRepository->findOneBy(['maman' => $maman], ['dateCreation' => 'DESC']);
        $grossesseConseil = null;
        $grossesseAlertes = [];
        $bebeAgeMois      = null;
        $bebeConseil      = null;
        $normesBebe       = null;
        $evaluationPoids  = null;
        $evaluationTaille = null;
        $prisePoids       = null;
        $evaluationPrise  = null;

        if ($grossesse) {
            $semaine = $grossesse->getSemaineActuelle();
            $statut  = $grossesse->getStatutGrossesse();

            if ($statut !== 'terminee' && $semaine !== null) {
                $grossesseConseil = $conseilsSuiviService->conseilsGrossesse($semaine);

                if ($statut === 'aRisque') {
                    $grossesseAlertes[] = 'Grossesse déclarée à risque : un suivi rapproché avec votre médecin est recommandé.';
                }
                if ($grossesse->getTypeGrossesse() === 'multiple') {
                    $grossesseAlertes[] = 'Grossesse multiple : contrôles prénataux plus fréquents conseillés.';
                }

                $poidsAvant  = $maman->getPoids();
                $poidsActuel = $grossesse->getPoidsActuel();
                $prisePoids  = ($poidsAvant && $poidsActuel)
                    ? round($poidsActuel - $poidsAvant, 1)
                    : null;

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

                $sexe   = $grossesse->getSexeBebe();
                $normes = [
                    'M' => ['poids_min' => 2.9, 'poids_max' => 4.0, 'taille_min' => 48.0, 'taille_max' => 52.0],
                    'F' => ['poids_min' => 2.8, 'poids_max' => 3.8, 'taille_min' => 47.0, 'taille_max' => 51.0],
                ];
                $normesBebe = $normes[$sexe] ?? $normes['M'];

                $poidsBebe  = $grossesse->getPoidsNaissance();
                $tailleBebe = $grossesse->getTailleNaissance();

                $evaluationPoids = 'normal';
                if ($poidsBebe !== null) {
                    if ($poidsBebe < $normesBebe['poids_min'])     $evaluationPoids = 'faible';
                    elseif ($poidsBebe > $normesBebe['poids_max']) $evaluationPoids = 'eleve';
                }

                $evaluationTaille = 'normal';
                if ($tailleBebe !== null) {
                    if ($tailleBebe < $normesBebe['taille_min'])     $evaluationTaille = 'faible';
                    elseif ($tailleBebe > $normesBebe['taille_max']) $evaluationTaille = 'eleve';
                }
            }
        }

        // ── Calculateur nutritionnel ──────────────────────────────
        $nutrition = null;
        if ($maman->getPoids() && $maman->getTaille()) {
            $nutrition = $nutritionService->calculerBesoins(
                $maman->getPoids(),
                $maman->getTaille(),
                $imc,
                $grossesse?->getTrimestreActuel() ?? 1,
                $maman->getAge()
            );
        }

        // ── Compte à rebours accouchement ────────────────────────
        $compteARebours = null;
        if ($grossesse && $grossesse->getDateAccouchementPrevue()) {
            $dateAccouchement = \DateTime::createFromImmutable($grossesse->getDateAccouchementPrevue());
            $maintenant       = new \DateTime();
            $diff             = $maintenant->diff($dateAccouchement);
            $joursRestants    = (int) $diff->days;

            if (!$diff->invert && $joursRestants <= 7) {
                $compteARebours = [
                    'date'  => $dateAccouchement->format('Y-m-d'),
                    'jours' => $joursRestants,
                ];
            }
        }

        // ── Données bébé selon la semaine (image par mois) ───────
        $bebeSemaine = null;
        if ($grossesse
            && $grossesse->getStatutGrossesse() !== 'terminee'
            && $grossesse->getSemaineActuelle() !== null
        ) {
            $bebeSemaine = $bebeSemaineService->getSemaine(
                $grossesse->getSemaineActuelle()
            );
        }

        return $this->render('pages/mon_profil_maman.html.twig', [
            'maman'             => $maman,
            'mode'              => 'show',
            'imc'               => $imc,
            'imc_categorie'     => $imcCategorie,
            'imc_alerte'        => $imcAlerte,
            'conseils'          => $conseils,
            'grossesse'         => $grossesse,
            'grossesse_conseil' => $grossesseConseil,
            'grossesse_alertes' => $grossesseAlertes,
            'bebe_age_mois'     => $bebeAgeMois,
            'bebe_conseil'      => $bebeConseil,
            'normes_bebe'       => $normesBebe,
            'evaluation_poids'  => $evaluationPoids,
            'evaluation_taille' => $evaluationTaille,
            'poids_avant'       => $maman->getPoids() ?? null,
            'poids_actuel_g'    => $grossesse?->getPoidsActuel() ?? null,
            'prise_poids'       => $prisePoids,
            'evaluation_prise'  => $evaluationPrise,
            'nutrition'         => $nutrition,
            'compte_a_rebours'  => $compteARebours,
            'bebe_semaine'      => $bebeSemaine,
        ]);
    }

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
            'form'  => $form,
            'mode'  => 'edit',
        ]);
    }

    #[Route('/suivi_grossesse/{id}/supprimer', name: 'app_suivi_grossesse_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function suiviGrossesseDelete(Request $request, Maman $maman, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $maman->getId(), $request->request->getString('_token'))) {
            $entityManager->remove($maman);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_suivi_grossesse_creer', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/suivi_grossesse/{id}/chatbot', name: 'app_chatbot_ask', methods: ['POST'])]
    public function chatbotAsk(
        Request $request,
        Maman $maman,
        ChatbotService $chatbotService,
        GrosesseRepository $grosesseRepository,
        SessionInterface $session
    ): JsonResponse {
        $decoded  = json_decode($request->getContent(), true);
        $data     = is_array($decoded) ? $decoded : [];

        // ✅ is_string() au lieu de isset() + cast → niveau 9
        $rawQuestion = $data['question'] ?? null;
        $question    = is_string($rawQuestion) ? $rawQuestion : '';

        if (empty($question)) {
            return new JsonResponse(['error' => 'Question vide'], 400);
        }

        $grossesse = $grosesseRepository->findOneBy(['maman' => $maman], ['dateCreation' => 'DESC']);

        $context = [
            'groupeSanguin' => $maman->getGroupeSanguin(),
            'maladies'      => $maman->getMaladiesChroniques(),
            'allergies'     => $maman->getAllergies(),
            'semaine'       => $grossesse?->getSemaineActuelle(),
            'trimestre'     => $grossesse?->getTrimestreActuel(),
        ];

        $sessionKey = 'chat_history_maman_' . $maman->getId();
        $history    = $session->get($sessionKey, []);
        $history    = is_array($history) ? $history : [];

        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        $reponse   = $chatbotService->ask($question, $context, $history);
        $history[] = ['role' => 'user',  'content' => $question];
        $history[] = ['role' => 'model', 'content' => $reponse];
        $session->set($sessionKey, $history);

        return new JsonResponse(['reponse' => $reponse]);
    }

    #[Route('/suivi_grossesse/{id}/chatbot/reset', name: 'app_chatbot_reset', methods: ['POST'])]
    public function chatbotReset(Maman $maman, SessionInterface $session): JsonResponse
    {
        $session->remove('chat_history_maman_' . $maman->getId());
        return new JsonResponse(['status' => 'ok', 'message' => 'Conversation réinitialisée 💕']);
    }

    #[Route('/suivi_grossesse/{id}/checklist', name: 'app_checklist', methods: ['GET'])]
    public function checklist(Maman $maman, SessionInterface $session): Response
    {
        $sessionKey = 'checklist_maman_' . $maman->getId();
        $checked    = $session->get($sessionKey, []);
        $checked    = is_array($checked) ? $checked : [];

        return $this->render('pages/checklist.html.twig', [
            'maman'   => $maman,
            'checked' => $checked,
        ]);
    }

    #[Route('/suivi_grossesse/{id}/checklist/toggle', name: 'app_checklist_toggle', methods: ['POST'])]
    public function checklistToggle(Request $request, Maman $maman, SessionInterface $session): JsonResponse
    {
        $decoded = json_decode($request->getContent(), true);
        $data    = is_array($decoded) ? $decoded : [];

        // ✅ is_string() → niveau 9
        $rawItem = $data['item'] ?? null;
        $itemId  = is_string($rawItem) ? $rawItem : null;

        if ($itemId === null || $itemId === '') {
            return new JsonResponse(['error' => 'Item manquant'], 400);
        }

        $sessionKey = 'checklist_maman_' . $maman->getId();
        $checked    = $session->get($sessionKey, []);
        $checked    = is_array($checked) ? $checked : [];

        if (in_array($itemId, $checked, true)) {
            $checked = array_values(array_filter($checked, fn($i) => $i !== $itemId));
        } else {
            $checked[] = $itemId;
        }

        $session->set($sessionKey, $checked);
        return new JsonResponse(['checked' => $checked]);
    }

    #[Route('/suivi_grossesse/{id}/checklist/reset', name: 'app_checklist_reset', methods: ['POST'])]
    public function checklistReset(Maman $maman, SessionInterface $session): JsonResponse
    {
        $session->remove('checklist_maman_' . $maman->getId());
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/suivi_grossesse/{id}/prenom', name: 'app_prenom_calculateur', methods: ['POST'])]
    public function prenomCalculateur(
        Request $request,
        Maman $maman,
        ChatbotService $chatbotService
    ): JsonResponse {
        $decoded = json_decode($request->getContent(), true);
        $data    = is_array($decoded) ? $decoded : [];

        // ✅ is_string() → niveau 9
        $rawPrenom     = $data['prenom']     ?? null;
        $rawNomFamille = $data['nomFamille'] ?? null;
        $prenom        = is_string($rawPrenom)     ? trim($rawPrenom)     : '';
        $nomFamille    = is_string($rawNomFamille) ? trim($rawNomFamille) : '';

        if ($prenom === '') {
            return new JsonResponse(['error' => 'Prénom manquant'], 400);
        }

        $question = "
Analyse le prénom \"$prenom\"" . ($nomFamille !== '' ? " avec le nom de famille \"$nomFamille\"" : "") . ".

Réponds UNIQUEMENT en JSON valide (sans markdown, sans backticks) avec cette structure exacte :
{
  \"prenom\": \"$prenom\",
  \"genre\": \"masculin ou féminin ou mixte\",
  \"origine\": \"origine du prénom\",
  \"signification\": \"signification détaillée\",
  \"popularite\": \"description de la popularité en France et Tunisie\",
  \"rang\": \"rang approximatif (ex: Top 10, Top 50, Rare...)\",
  \"similaires\": [\"prénom1\", \"prénom2\", \"prénom3\", \"prénom4\", \"prénom5\"],
  \"compatibilite\": \"" . ($nomFamille !== '' ? "analyse de compatibilité avec $nomFamille" : "non demandée") . "\",
  \"score_compatibilite\": " . ($nomFamille !== '' ? "score de 1 à 10" : "0") . ",
  \"anecdote\": \"une anecdote ou fait intéressant sur ce prénom\"
}
";

        $reponse = $chatbotService->ask($question);
        $reponse = trim((string) preg_replace('/```json|```/', '', $reponse));

        $decodedResult = json_decode($reponse, true);
        if (!is_array($decodedResult)) {
            return new JsonResponse(['error' => 'Erreur analyse'], 500);
        }

        return new JsonResponse($decodedResult);
    }

    /** @return array<string> */
    private function getConseilsSante(Maman $maman): array
    {
        $conseils = [];
        $imc      = $maman->getImc();

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
            $conseils[] = 'Arrêter de fumer est l\'un des meilleurs gestes pour vous et bébé. N\'hésitez pas à demander de l\'aide (substituts, accompagnement).';
        }
        if ($maman->isConsommationAlcool()) {
            $conseils[] = 'Zéro alcool pendant la grossesse est recommandé. Nous pouvons vous orienter vers un accompagnement si besoin.';
        }

        $niveau = $maman->getNiveauActivitePhysique();
        if ($niveau === 'Sédentaire' || $niveau === 'Léger') {
            $conseils[] = 'Marcher 20–30 minutes par jour est bénéfique pendant la grossesse. Adaptez l\'intensité à votre forme.';
        }

        if (empty($conseils)) {
            $conseils[] = 'Continuez à prendre soin de vous : alimentation équilibrée, repos et suivi régulier.';
        }

        return $conseils;
    }
}