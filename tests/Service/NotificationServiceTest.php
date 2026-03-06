<?php

namespace App\Tests\Service;

use App\Entity\ConsultationCreneau;
use App\Entity\ReservationClient;
use App\Service\NotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

/**
 * Tests unitaires — NotificationService
 * Vérifie la logique métier SMS, Email et WhatsApp sans appels externes réels.
 * Sprint Web 2 — Maternia
 */
class NotificationServiceTest extends TestCase
{
    // =========================================================
    // HELPERS
    // =========================================================

    /** @return MockObject&MailerInterface */
    private function createMailerMock(): MailerInterface
    {
        return $this->createMock(MailerInterface::class);
    }

    /** @return MockObject&Environment */
    private function createTwigMock(string $output = '<html>test</html>'): Environment
    {
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn($output);
        return $twig;
    }

    /** @return MockObject&LoggerInterface */
    private function createLoggerMock(): LoggerInterface
    {
        return $this->createMock(LoggerInterface::class);
    }

    private function createReservationComplete(): ReservationClient
    {
        $reservation = new ReservationClient();
        $reservation->setNomClient('Gharbi');
        $reservation->setPrenomClient('Amira');
        $reservation->setEmailClient('amira.gharbi@maternia.tn');
        $reservation->setTelephoneClient('25001122');
        $reservation->setTypePatient('enceinte');
        $reservation->setMoisGrossesse(6);
        $reservation->setStatutReservation('confirmée');
        $reservation->setReference('MAT-TEST-0001');
        $reservation->setDateReservation(new \DateTime('2025-06-15 10:00:00'));
        return $reservation;
    }

    private function createServiceSansTwilio(
        ?MailerInterface $mailer = null,
        ?Environment $twig = null,
        ?LoggerInterface $logger = null
    ): NotificationService {
        return new NotificationService(
            $mailer ?? $this->createMailerMock(),
            $twig ?? $this->createTwigMock(),
            $logger ?? $this->createLoggerMock(),
            'noreply@maternia.tn',  // fromEmail
            'Maternia Clinique',     // fromName
            null,  // twilioSid
            null,  // twilioToken
            null,  // twilioSmsFrom
            null,  // twilioWhatsAppFrom
        );
    }

    // =========================================================
    // 1. SMS — NUMÉRO MANQUANT
    // =========================================================

    public function testSmsEchoueQuandNumeroManquant(): void
    {
        $reservation = new ReservationClient();
        $reservation->setNomClient('Test');
        $reservation->setPrenomClient('Test');
        $reservation->setEmailClient('test@test.tn');
        $reservation->setStatutReservation('confirmée');
        $reservation->setReference('REF-001');
        $reservation->setDateReservation(new \DateTime());
        // Pas de téléphone

        $service = $this->createServiceSansTwilio();
        $result = $service->sendConfirmationSms($reservation);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('téléphone', $result['message']);
    }

    public function testSmsReussitEnModeSimulationSansTwilio(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();

        $result = $service->sendConfirmationSms($reservation);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['simulated']);
    }

    public function testSmsSimulationContiendNumeroFormate(): void
    {
        $reservation = $this->createReservationComplete(); // téléphone '25001122'
        $service = $this->createServiceSansTwilio();

        $result = $service->sendConfirmationSms($reservation);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('+216', $result['message']); // Format tunisien ajouté
    }

    public function testSmsAvecCustomPhone(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();

        $result = $service->sendConfirmationSms($reservation, null, '98765432');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['simulated']);
    }

    public function testSmsAvecMeetLink(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();

        $result = $service->sendConfirmationSms($reservation, 'https://meet.google.com/abc-def-ghi');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('meet.google.com', $result['content']);
    }

    public function testSmsContiendReference(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();

        $result = $service->sendConfirmationSms($reservation);

        $this->assertStringContainsString('MAT-TEST-0001', $result['content']);
    }

    public function testSmsRetourneArrayAvecCleSuccess(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();
        $result = $service->sendConfirmationSms($reservation);

        $this->assertArrayHasKey('success', $result);
    }

    // =========================================================
    // 2. SMS — FORMATAGE DU NUMÉRO
    // =========================================================

    public function testFormatageNumeroTunisien8Chiffres(): void
    {
        $reservation = $this->createReservationComplete();
        $reservation->setTelephoneClient('25001122');
        $service = $this->createServiceSansTwilio();

        $result = $service->sendConfirmationSms($reservation);

        // Le formatage +216 est appliqué → message contient +216
        $this->assertStringContainsString('+21625001122', $result['message']);
    }

    public function testFormatageNumeroDejaAvecIndicatif(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();

        // Custom phone déjà avec indicatif (11 chiffres 216XXXXXXXX)
        $result = $service->sendConfirmationSms($reservation, null, '21625001122');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('+21625001122', $result['message']);
    }

    // =========================================================
    // 3. EMAIL — CONFIGURATION
    // =========================================================

    public function testEmailEchoueQuandEmailManquant(): void
    {
        $reservation = new ReservationClient();
        $reservation->setNomClient('Test');
        $reservation->setPrenomClient('Test');
        $reservation->setStatutReservation('confirmée');
        $reservation->setReference('REF-002');
        $reservation->setDateReservation(new \DateTime());
        // Pas d'email

        $service = $this->createServiceSansTwilio();
        $result = $service->sendConfirmationEmail($reservation);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('email', $result['message']);
    }

    public function testEmailEnvoiAvecMailerMock(): void
    {
        $mailer = $this->createMailerMock();
        $mailer->expects($this->once())->method('send');

        $service = $this->createServiceSansTwilio($mailer);
        $reservation = $this->createReservationComplete();
        $result = $service->sendConfirmationEmail($reservation);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('amira.gharbi@maternia.tn', $result['to']);
    }

    public function testEmailAvecCustomEmail(): void
    {
        $mailer = $this->createMailerMock();
        $mailer->expects($this->once())->method('send');

        $service = $this->createServiceSansTwilio($mailer);
        $reservation = $this->createReservationComplete();
        $result = $service->sendConfirmationEmail($reservation, null, 'autre@example.com');

        $this->assertTrue($result['success']);
        $this->assertSame('autre@example.com', $result['to']);
    }

    public function testEmailAvecMeetLink(): void
    {
        $mailer = $this->createMailerMock();
        $twig = $this->createTwigMock();
        // On vérifie que le meetLink est passé à Twig
        $twig->expects($this->once())
            ->method('render')
            ->with(
                'emails/reservation_confirmation.html.twig',
                $this->callback(function ($params) {
                    return isset($params['meetLink']) && $params['meetLink'] === 'https://meet.google.com/xyz-abc';
                })
            )
            ->willReturn('<html>email</html>');

        $service = $this->createServiceSansTwilio($mailer, $twig);
        $reservation = $this->createReservationComplete();
        $result = $service->sendConfirmationEmail($reservation, 'https://meet.google.com/xyz-abc');

        $this->assertTrue($result['success']);
    }

    public function testEmailCaptureLExceptionMailer(): void
    {
        $mailer = $this->createMailerMock();
        $mailer->method('send')->willThrowException(new \RuntimeException('SMTP error'));

        $logger = $this->createLoggerMock();
        $logger->expects($this->once())->method('error');

        $service = $this->createServiceSansTwilio($mailer, null, $logger);
        $reservation = $this->createReservationComplete();
        $result = $service->sendConfirmationEmail($reservation);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('SMTP error', $result['message']);
    }

    public function testEmailRetourneArrayAvecCleSuccess(): void
    {
        $service = $this->createServiceSansTwilio();
        $reservation = $this->createReservationComplete();
        $result = $service->sendConfirmationEmail($reservation);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
    }

    // =========================================================
    // 4. WHATSAPP — SIMULATION (sans Twilio)
    // =========================================================

    public function testWhatsAppEchoueQuandNumeroManquant(): void
    {
        $reservation = new ReservationClient();
        $reservation->setNomClient('W');
        $reservation->setPrenomClient('T');
        $reservation->setEmailClient('wt@test.tn');
        $reservation->setStatutReservation('confirmée');
        $reservation->setReference('WA-001');
        $reservation->setDateReservation(new \DateTime());

        $service = $this->createServiceSansTwilio();
        $result = $service->sendWhatsAppMessage($reservation);

        $this->assertFalse($result['success']);
    }

    public function testWhatsAppSimulationGenereUnLien(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();

        $result = $service->sendWhatsAppMessage($reservation);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['simulated']);
        $this->assertArrayHasKey('waLink', $result);
        $this->assertStringContainsString('wa.me', $result['waLink']);
    }

    public function testWhatsAppLienContientNumeroPropre(): void
    {
        $reservation = $this->createReservationComplete(); // téléphone '25001122'
        $service = $this->createServiceSansTwilio();

        $result = $service->sendWhatsAppMessage($reservation);

        // Le lien wa.me doit contenir les chiffres sans +
        $this->assertStringContainsString('21625001122', $result['waLink']);
    }

    public function testWhatsAppAvecMeetLinkDansMessage(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();

        $result = $service->sendWhatsAppMessage($reservation, 'https://meet.google.com/abc-xyz');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('meet.google.com', $result['waLink']);
    }

    public function testWhatsAppRetourneArrayAvecCleSuccess(): void
    {
        $reservation = $this->createReservationComplete();
        $service = $this->createServiceSansTwilio();
        $result = $service->sendWhatsAppMessage($reservation);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
    }

    // =========================================================
    // 5. TWILIO CONFIGURÉ → TENTATIVE RÉELLE (mais URL fail proprement)
    // =========================================================

    public function testSmsAvecTwilioConfigureCaptureLErreurAPI(): void
    {
        $service = new NotificationService(
            $this->createMailerMock(),
            $this->createTwigMock(),
            $this->createLoggerMock(),
            'noreply@maternia.tn',
            'Maternia',
            'ACfake_sid_000',       // twilioSid (faux → API rejette)
            'fake_token_000',       // twilioToken (faux)
            '+15005550006',         // twilioSmsFrom (numéro test Twilio magic number)
        );

        $reservation = $this->createReservationComplete();
        $result = $service->sendConfirmationSms($reservation);

        // L'appel curl échoue (SID/Token faux) → résultat avec success=false ou true simulé
        // Dans tous les cas, pas de crash PHP
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
    }
}
