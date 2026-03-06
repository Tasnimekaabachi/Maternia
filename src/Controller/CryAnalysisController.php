<?php

namespace App\Controller;

use App\Entity\DemandeBabySitter;
use App\Repository\OffreBabySitterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CryAnalysisController extends AbstractController
{
    private const MAIL_FROM = 'noreply@maternia.tn';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly OffreBabySitterRepository $offreBabySitterRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly KernelInterface $kernel,
        private readonly TexterInterface $texter,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $mailerDsn,
        private readonly string $twilioDsn,
    ) {
    }

    private function isMailConfigured(): bool
    {
        $dsn = trim($this->mailerDsn);
        return $dsn !== '' && $dsn !== 'null' && !str_contains($dsn, 'null://null');
    }

    private function isSmsConfigured(): bool
    {
        $dsn = trim($this->twilioDsn);
        if ($dsn === '') {
            return false;
        }
        return !str_contains($dsn, 'YOUR_ACCOUNT_SID') && !str_contains($dsn, 'YOUR_AUTH_TOKEN');
    }

    #[Route('/api/cry-send-mail-only', name: 'api_cry_send_mail_only', methods: ['POST'])]
    public function sendMailOnly(Request $request): Response
    {
        try {
            $offreId = $request->query->getInt('offreId', 0);
            $babyName = trim((string) $request->query->get('babyName', ''));
            $parentName = trim((string) $request->query->get('parentName', ''));
            $parentEmail = trim((string) $request->query->get('parentEmail', ''));

            if ($offreId <= 0) {
                return $this->json(['ok' => false, 'error' => 'Choisissez une babysitter'], Response::HTTP_BAD_REQUEST);
            }

            $offre = $this->offreBabySitterRepository->find($offreId);
            if ($offre === null) {
                return $this->json(['ok' => false, 'error' => 'Offre introuvable'], Response::HTTP_NOT_FOUND);
            }

            $babyPart = $babyName !== '' ? ("Bébé: {$babyName}. ") : '';

            $demande = new DemandeBabySitter();
            $demande->setOffre($offre);
            $demande->setNomParent($parentName !== '' ? $parentName : 'Maternia (mailing)');
            $demande->setEmailParent($parentEmail !== '' ? $parentEmail : 'noreply@maternia.tn');
            $demande->setDateDemande(new \DateTimeImmutable());
            $demande->setStatut('ALERTE_MAIL');
            $demande->setMessage(
                $babyPart
                . "Alerte manuelle (mailing uniquement). "
                . "Merci de vérifier le bébé."
            );

            $this->entityManager->persist($demande);
            $this->entityManager->flush();

            $emailBabysitterSent = false;
            $emailParentSent = false;
            $configHint = null;

            if (!$this->isMailConfigured()) {
                $configHint = 'Email non configuré : définir MAILER_DSN dans .env.local (ex: sendmail://default ou smtp://...)';
            } else {
                $babysitterEmail = $offre->getEmail();
                if ($babysitterEmail !== null && $babysitterEmail !== '') {
                    try {
                        $babyLabel = $babyName !== '' ? $babyName : 'le bébé';
                        $email = (new Email())
                            ->from(self::MAIL_FROM)
                            ->to($babysitterEmail)
                            ->subject('[Maternia] Alerte - ' . $babyLabel)
                            ->text(
                                "Bonjour,\n\n"
                                . "Alerte manuelle (mailing).\n"
                                . ($babyPart ?: '')
                                . "Merci de vérifier le bébé. Les parents ont été prévenus.\n\n"
                                . "Cordialement,\nMaternia"
                            );
                        $this->mailer->send($email);
                        $emailBabysitterSent = true;
                    } catch (\Throwable $e) {
                        $this->logger->warning('Envoi email alerte babysitter (mailing) échoué: {message}', [
                            'message' => $e->getMessage(),
                            'to' => $babysitterEmail,
                        ]);
                    }
                }

                if ($parentEmail !== '') {
                    try {
                        $babyLabel = $babyName !== '' ? $babyName : 'votre bébé';
                        $nomBabysitter = $offre->getNomBabysitter() ?? 'la babysitter';
                        $ville = $offre->getVille() ?? '';
                        $email = (new Email())
                            ->from(self::MAIL_FROM)
                            ->to($parentEmail)
                            ->subject('[Maternia] Veuillez récupérer votre bébé')
                            ->text(
                                "Bonjour" . ($parentName !== '' ? " {$parentName}" : '') . ",\n\n"
                                . "Alerte Maternia : {$babyLabel} (mailing).\n\n"
                                . "La babysitter ({$nomBabysitter}" . ($ville !== '' ? ", {$ville}" : '') . ") a été alertée. "
                                . "Merci de revenir récupérer votre bébé dès que possible.\n\n"
                                . "Cordialement,\nMaternia"
                            );
                        $this->mailer->send($email);
                        $emailParentSent = true;
                    } catch (\Throwable $e) {
                        $this->logger->warning('Envoi email alerte parent (mailing) échoué: {message}', [
                            'message' => $e->getMessage(),
                            'to' => $parentEmail,
                        ]);
                    }
                }
            }

            $payload = [
                'ok' => true,
                'emailBabysitterSent' => $emailBabysitterSent,
                'emailParentSent' => $emailParentSent,
            ];
            if ($configHint !== null) {
                $payload['configHint'] = $configHint;
            }
            return $this->json($payload);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur mailing (send-mail-only): {message}', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'emailBabysitterSent' => false,
                'emailParentSent' => false,
            ], Response::HTTP_OK);
        }
    }

    #[Route('/api/cry-classify', name: 'api_cry_classify', methods: ['POST'])]
    public function classify(Request $request): Response
    {
        $rawAudio = $request->getContent();

        if ($rawAudio === '') {
            return $this->json(
                ['error' => 'Aucun audio reçu'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            // Appel au micro‑service Python de classification des pleurs
            $response = $this->httpClient->request('POST', 'http://127.0.0.1:8001/predict-cry', [
                'body' => $rawAudio,
                'headers' => [
                    'Content-Type' => 'application/octet-stream',
                    'Accept' => 'application/json',
                ],
                'timeout' => 5.0,
            ]);

            $data = $response->toArray(false);

            $label = (string) ($data['label'] ?? 'inconnu');
            $confidence = $data['confidence'] ?? null;

            $offreId = $request->query->getInt('offreId', 0);
            $babyName = trim((string) $request->query->get('babyName', ''));
            $parentName = trim((string) $request->query->get('parentName', ''));
            $parentEmail = trim((string) $request->query->get('parentEmail', ''));

            $alertCreated = false;
            $smsSent = false;
            $emailBabysitterSent = false;
            $emailParentSent = false;
            $configHints = [];

            // Déclenche une alerte automatique si le bébé a un besoin détecté (pas "calme")
            if ($offreId > 0 && mb_strtolower($label) !== 'calme' && $this->canCreateAlert($offreId)) {
                $offre = $this->offreBabySitterRepository->find($offreId);

                if ($offre !== null) {
                    $demande = new DemandeBabySitter();
                    $demande->setOffre($offre);
                    $demande->setNomParent($parentName !== '' ? $parentName : 'Maternia (auto)');
                    $demande->setEmailParent($parentEmail !== '' ? $parentEmail : 'noreply@maternia.tn');
                    $demande->setDateDemande(new \DateTimeImmutable());
                    $demande->setStatut('ALERTE_AUDIO');

                    $babyPart = $babyName !== '' ? ("Bébé: {$babyName}. ") : '';
                    $confPart = $confidence !== null ? ("Confiance: {$confidence}. ") : '';

                    $demande->setMessage(
                        $babyPart
                        . "Alerte automatique suite à l’analyse des pleurs. "
                        . "État détecté: {$label}. "
                        . $confPart
                        . "Merci de vérifier le bébé dès que possible."
                    );

                    $this->entityManager->persist($demande);
                    $this->entityManager->flush();

                    $this->markAlertCreated($offreId);
                    $alertCreated = true;

                    // Envoi SMS à la babysitter (numéro au format E.164, ex: +21671234567)
                    if (!$this->isSmsConfigured()) {
                        $configHints[] = 'SMS non configuré : remplacer YOUR_ACCOUNT_SID et YOUR_AUTH_TOKEN dans TWILIO_DSN (.env.local)';
                    } else {
                        $to = $this->normalizePhone((string) $offre->getTelephone());
                        if ($to !== null) {
                            $smsText = $babyPart
                                . "Alerte pleurs: {$label}. "
                                . ($confidence !== null ? "Conf: {$confidence}. " : "")
                                . "Merci de vérifier le bébé.";

                            try {
                                $this->texter->send(new SmsMessage($to, $smsText, null, 'twilio'));
                                $smsSent = true;
                            } catch (\Throwable $e) {
                                $this->logger->warning('Envoi SMS alerte pleurs échoué: {message}', [
                                    'message' => $e->getMessage(),
                                    'to' => $to,
                                ]);
                                $smsSent = false;
                            }
                        }
                    }

                    // Email à la babysitter (si email renseigné sur l'offre)
                    if (!$this->isMailConfigured()) {
                        $configHints[] = 'Email non configuré : définir MAILER_DSN dans .env.local (ex: sendmail://default ou smtp://...)';
                    } else {
                        $babysitterEmail = $offre->getEmail();
                        if ($babysitterEmail !== null && $babysitterEmail !== '') {
                            try {
                                $babyLabel = $babyName !== '' ? $babyName : 'le bébé';
                                $email = (new Email())
                                    ->from(self::MAIL_FROM)
                                    ->to($babysitterEmail)
                                    ->subject('[Maternia] Alerte pleurs - ' . $babyLabel)
                                    ->text(
                                        "Bonjour,\n\n"
                                        . "Alerte automatique suite à l'analyse des pleurs.\n"
                                        . ($babyPart ?: '')
                                        . "État détecté : {$label}.\n"
                                        . "Merci de vérifier le bébé. Les parents ont été prévenus pour le retour du bébé à la maison.\n\n"
                                        . "Cordialement,\nMaternia"
                                    );
                                $this->mailer->send($email);
                                $emailBabysitterSent = true;
                            } catch (\Throwable $e) {
                                $this->logger->warning('Envoi email alerte babysitter échoué: {message}', [
                                    'message' => $e->getMessage(),
                                    'to' => $babysitterEmail,
                                ]);
                            }
                        }

                        // Email au parent : retour du bébé à la maison
                        if ($parentEmail !== '') {
                            try {
                                $babyLabel = $babyName !== '' ? $babyName : 'votre bébé';
                                $nomBabysitter = $offre->getNomBabysitter() ?? 'la babysitter';
                                $ville = $offre->getVille() ?? '';
                                $email = (new Email())
                                    ->from(self::MAIL_FROM)
                                    ->to($parentEmail)
                                    ->subject('[Maternia] Veuillez récupérer votre bébé')
                                    ->text(
                                        "Bonjour" . ($parentName !== '' ? " {$parentName}" : '') . ",\n\n"
                                        . "Alerte Maternia : {$babyLabel} – état détecté : {$label}.\n\n"
                                        . "La babysitter ({$nomBabysitter}" . ($ville !== '' ? ", {$ville}" : '') . ") a été alertée. "
                                        . "Merci de revenir récupérer votre bébé à la maison dès que possible.\n\n"
                                        . "Cordialement,\nMaternia"
                                    );
                                $this->mailer->send($email);
                                $emailParentSent = true;
                            } catch (\Throwable $e) {
                                $this->logger->warning('Envoi email alerte parent échoué: {message}', [
                                    'message' => $e->getMessage(),
                                    'to' => $parentEmail,
                                ]);
                            }
                        }
                    }
                }
            }

            $payload = [
                'label' => $label,
                'confidence' => $confidence,
                'alertCreated' => $alertCreated,
                'smsSent' => $smsSent,
                'emailBabysitterSent' => $emailBabysitterSent,
                'emailParentSent' => $emailParentSent,
            ];
            if ($configHints !== []) {
                $payload['configHint'] = implode(' ', $configHints);
            }
            return $this->json($payload);
        } catch (\Throwable $e) {
            return $this->json(
                ['error' => 'Erreur service ML : ' . $e->getMessage()],
                Response::HTTP_BAD_GATEWAY
            );
        }
    }

    private function normalizePhone(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // Garde uniquement + et chiffres
        $clean = preg_replace('/(?!^\+)[^\d]/', '', $raw);
        if ($clean === null || $clean === '' || $clean === '+') {
            return null;
        }

        // Twilio exige le format E.164 (ex: +21671234567 pour la Tunisie)
        if (str_starts_with($clean, '+')) {
            return $clean;
        }

        // Numéro sans indicatif : on suppose Tunisie (+216)
        // Supprimer un éventuel 0 en tête (ex: 071234567 -> 71234567)
        $clean = ltrim($clean, '0');
        if ($clean === '') {
            return null;
        }
        // Tunisie = 8 chiffres pour mobile (2x, 4x, 5x, 9x...)
        if (strlen($clean) <= 8) {
            return '+216' . $clean;
        }
        return '+' . $clean;
    }

    private function canCreateAlert(int $offreId): bool
    {
        $cooldownSeconds = 20;
        $path = $this->getAlertThrottlePath($offreId);

        if (!is_file($path)) {
            return true;
        }

        $json = @file_get_contents($path);
        if ($json === false || $json === '') {
            return true;
        }

        $data = json_decode($json, true);
        $lastAt = is_array($data) ? (int) ($data['lastAt'] ?? 0) : 0;

        return (time() - $lastAt) >= $cooldownSeconds;
    }

    private function markAlertCreated(int $offreId): void
    {
        $path = $this->getAlertThrottlePath($offreId);
        $dir = \dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        @file_put_contents($path, json_encode(['lastAt' => time()]));
    }

    private function getAlertThrottlePath(int $offreId): string
    {
        return $this->kernel->getProjectDir() . '/var/cry_alert_throttle/offre_' . $offreId . '.json';
    }
}

