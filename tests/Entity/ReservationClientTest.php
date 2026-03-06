<?php

namespace App\Tests\Entity;

use App\Entity\ConsultationCreneau;
use App\Entity\ReservationClient;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires — Entité ReservationClient
 * Maternia — Sprint Web 2
 */
class ReservationClientTest extends TestCase
{
    // ─────────────────────────────────────────────
    // 1. CONSTRUCTEUR — valeurs automatiques
    // ─────────────────────────────────────────────

    public function testIdNullParDefaut(): void
    {
        $reservation = new ReservationClient();
        $this->assertNull($reservation->getId());
    }

    public function testCreatedAtInitialiseAutomatiquement(): void
    {
        $reservation = new ReservationClient();
        $this->assertInstanceOf(\DateTimeImmutable::class, $reservation->getCreatedAt());
    }

    public function testUpdatedAtInitialiseAutomatiquement(): void
    {
        $reservation = new ReservationClient();
        $this->assertInstanceOf(\DateTimeImmutable::class, $reservation->getUpdatedAt());
    }

    public function testCreatedAtDateRecente(): void
    {
        $avant = new \DateTimeImmutable();
        $reservation = new ReservationClient();
        $apres = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($avant, $reservation->getCreatedAt());
        $this->assertLessThanOrEqual($apres, $reservation->getCreatedAt());
    }

    // ─────────────────────────────────────────────
    // 2. NOM & PRÉNOM CLIENT
    // ─────────────────────────────────────────────

    public function testSetAndGetNomClient(): void
    {
        $reservation = new ReservationClient();
        $reservation->setNomClient('Ben Salah');
        $this->assertSame('Ben Salah', $reservation->getNomClient());
    }

    public function testSetAndGetPrenomClient(): void
    {
        $reservation = new ReservationClient();
        $reservation->setPrenomClient('Fatma');
        $this->assertSame('Fatma', $reservation->getPrenomClient());
    }

    public function testNomAvecCaracteresSpeciaux(): void
    {
        $reservation = new ReservationClient();
        $reservation->setNomClient("Ben M'Barek");
        $this->assertSame("Ben M'Barek", $reservation->getNomClient());
    }

    // ─────────────────────────────────────────────
    // 3. EMAIL CLIENT
    // ─────────────────────────────────────────────

    public function testSetAndGetEmailClient(): void
    {
        $reservation = new ReservationClient();
        $reservation->setEmailClient('fatma@example.com');
        $this->assertSame('fatma@example.com', $reservation->getEmailClient());
    }

    public function testEmailAvecSousdomaine(): void
    {
        $reservation = new ReservationClient();
        $reservation->setEmailClient('user@mail.maternia.tn');
        $this->assertSame('user@mail.maternia.tn', $reservation->getEmailClient());
    }

    // ─────────────────────────────────────────────
    // 4. TÉLÉPHONE CLIENT (exactement 8 chiffres)
    // ─────────────────────────────────────────────

    public function testSetAndGetTelephoneClient(): void
    {
        $reservation = new ReservationClient();
        $reservation->setTelephoneClient('22334455');
        $this->assertSame('22334455', $reservation->getTelephoneClient());
    }

    public function testTelephoneHuitChiffres(): void
    {
        $reservation = new ReservationClient();
        $reservation->setTelephoneClient('98765432');
        $this->assertSame(8, strlen($reservation->getTelephoneClient()));
    }

    public function testTelephoneContientSeulementChiffres(): void
    {
        $reservation = new ReservationClient();
        $reservation->setTelephoneClient('55667788');
        $this->assertMatchesRegularExpression('/^[0-9]{8}$/', $reservation->getTelephoneClient());
    }

    // ─────────────────────────────────────────────
    // 5. TYPE PATIENT
    // ─────────────────────────────────────────────

    public function testSetAndGetTypePatientEnceinte(): void
    {
        $reservation = new ReservationClient();
        $reservation->setTypePatient('enceinte');
        $this->assertSame('enceinte', $reservation->getTypePatient());
    }

    public function testSetAndGetTypePatientNouvelleMaman(): void
    {
        $reservation = new ReservationClient();
        $reservation->setTypePatient('nouvelle_maman');
        $this->assertSame('nouvelle_maman', $reservation->getTypePatient());
    }

    public function testSetAndGetTypePatientAutre(): void
    {
        $reservation = new ReservationClient();
        $reservation->setTypePatient('autre');
        $this->assertSame('autre', $reservation->getTypePatient());
    }

    // ─────────────────────────────────────────────
    // 6. MOIS GROSSESSE (nullable, entier)
    // ─────────────────────────────────────────────

    public function testSetAndGetMoisGrossesse(): void
    {
        $reservation = new ReservationClient();
        $reservation->setMoisGrossesse(5);
        $this->assertSame(5, $reservation->getMoisGrossesse());
    }

    public function testSetMoisGrossesseNull(): void
    {
        $reservation = new ReservationClient();
        $reservation->setMoisGrossesse(null);
        $this->assertNull($reservation->getMoisGrossesse());
    }

    public function testMoisGrossessePremierMois(): void
    {
        $reservation = new ReservationClient();
        $reservation->setMoisGrossesse(1);
        $this->assertSame(1, $reservation->getMoisGrossesse());
    }

    public function testMoisGrossesseDernierMois(): void
    {
        $reservation = new ReservationClient();
        $reservation->setMoisGrossesse(9);
        $this->assertSame(9, $reservation->getMoisGrossesse());
    }

    // ─────────────────────────────────────────────
    // 7. DATE NAISSANCE BÉBÉ (nullable)
    // ─────────────────────────────────────────────

    public function testSetAndGetDateNaissanceBebe(): void
    {
        $reservation = new ReservationClient();
        $date = new \DateTime('2026-08-15');
        $reservation->setDateNaissanceBebe($date);
        $this->assertSame($date, $reservation->getDateNaissanceBebe());
    }

    public function testSetDateNaissanceBebeNull(): void
    {
        $reservation = new ReservationClient();
        $reservation->setDateNaissanceBebe(null);
        $this->assertNull($reservation->getDateNaissanceBebe());
    }

    public function testDateNaissanceFuture(): void
    {
        $reservation = new ReservationClient();
        $date = new \DateTime('+2 months');
        $reservation->setDateNaissanceBebe($date);
        $this->assertGreaterThan(new \DateTime(), $reservation->getDateNaissanceBebe());
    }

    // ─────────────────────────────────────────────
    // 8. STATUT RÉSERVATION
    // ─────────────────────────────────────────────

    public function testSetAndGetStatutReservationEnAttente(): void
    {
        $reservation = new ReservationClient();
        $reservation->setStatutReservation('en_attente');
        $this->assertSame('en_attente', $reservation->getStatutReservation());
    }

    public function testSetAndGetStatutReservationConfirmee(): void
    {
        $reservation = new ReservationClient();
        $reservation->setStatutReservation('confirmée');
        $this->assertSame('confirmée', $reservation->getStatutReservation());
    }

    public function testSetAndGetStatutReservationAnnulee(): void
    {
        $reservation = new ReservationClient();
        $reservation->setStatutReservation('annulée');
        $this->assertSame('annulée', $reservation->getStatutReservation());
    }

    // ─────────────────────────────────────────────
    // 9. RÉFÉRENCE
    // ─────────────────────────────────────────────

    public function testSetAndGetReference(): void
    {
        $reservation = new ReservationClient();
        $reservation->setReference('MAT-2026-00042');
        $this->assertSame('MAT-2026-00042', $reservation->getReference());
    }

    public function testReferenceUniqueFormat(): void
    {
        $reservation = new ReservationClient();
        $ref = 'MAT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $reservation->setReference($ref);
        $this->assertMatchesRegularExpression('/^MAT-\d{4}-\d{4}$/', $reservation->getReference());
    }

    // ─────────────────────────────────────────────
    // 10. DATE DE RÉSERVATION
    // ─────────────────────────────────────────────

    public function testSetAndGetDateReservation(): void
    {
        $reservation = new ReservationClient();
        $date = new \DateTime('2026-04-01 14:00:00');
        $reservation->setDateReservation($date);
        $this->assertSame($date, $reservation->getDateReservation());
    }

    public function testDateReservationFormatCorrect(): void
    {
        $reservation = new ReservationClient();
        $date = new \DateTime('2026-03-15 09:30:00');
        $reservation->setDateReservation($date);
        $this->assertSame('2026-03-15', $reservation->getDateReservation()->format('Y-m-d'));
    }

    // ─────────────────────────────────────────────
    // 11. NOTES (nullable, TEXT)
    // ─────────────────────────────────────────────

    public function testSetAndGetNotes(): void
    {
        $reservation = new ReservationClient();
        $reservation->setNotes('Allergie aux anesthésiants.');
        $this->assertSame('Allergie aux anesthésiants.', $reservation->getNotes());
    }

    public function testSetNotesNull(): void
    {
        $reservation = new ReservationClient();
        $reservation->setNotes(null);
        $this->assertNull($reservation->getNotes());
    }

    public function testNotesLongues(): void
    {
        $reservation = new ReservationClient();
        $texte = str_repeat('Note importante. ', 200);
        $reservation->setNotes($texte);
        $this->assertSame($texte, $reservation->getNotes());
    }

    // ─────────────────────────────────────────────
    // 12. DATES createdAt / updatedAt (setters)
    // ─────────────────────────────────────────────

    public function testSetCreatedAt(): void
    {
        $reservation = new ReservationClient();
        $date = new \DateTimeImmutable('2026-01-01');
        $reservation->setCreatedAt($date);
        $this->assertSame($date, $reservation->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $reservation = new ReservationClient();
        $date = new \DateTimeImmutable('2026-02-20');
        $reservation->setUpdatedAt($date);
        $this->assertSame($date, $reservation->getUpdatedAt());
    }

    public function testSetUpdatedAtNull(): void
    {
        $reservation = new ReservationClient();
        $reservation->setUpdatedAt(null);
        $this->assertNull($reservation->getUpdatedAt());
    }

    // ─────────────────────────────────────────────
    // 13. RELATION — ConsultationCreneau (OneToOne)
    // ─────────────────────────────────────────────

    public function testSetAndGetConsultationCreneau(): void
    {
        $reservation = new ReservationClient();
        $creneau = new ConsultationCreneau();
        $creneau->setNomMedecin('Dr. Leila Gharbi');
        $creneau->setStatutReservation('disponible');

        $reservation->setConsultationCreneau($creneau);

        $this->assertSame($creneau, $reservation->getConsultationCreneau());
    }

    public function testConsultationCreneauNullParDefaut(): void
    {
        $reservation = new ReservationClient();
        $this->assertNull($reservation->getConsultationCreneau());
    }

    // ─────────────────────────────────────────────
    // 14. FLUENT INTERFACE (retourne static)
    // ─────────────────────────────────────────────

    public function testSetNomClientRetourneStatic(): void
    {
        $reservation = new ReservationClient();
        $result = $reservation->setNomClient('Test');
        $this->assertSame($reservation, $result);
    }

    public function testSetEmailClientRetourneStatic(): void
    {
        $reservation = new ReservationClient();
        $result = $reservation->setEmailClient('test@test.com');
        $this->assertSame($reservation, $result);
    }

    public function testSetStatutReservationRetourneStatic(): void
    {
        $reservation = new ReservationClient();
        $result = $reservation->setStatutReservation('confirmée');
        $this->assertSame($reservation, $result);
    }

    public function testSetReferenceRetourneStatic(): void
    {
        $reservation = new ReservationClient();
        $result = $reservation->setReference('MAT-0001');
        $this->assertSame($reservation, $result);
    }

    // ─────────────────────────────────────────────
    // TESTS SUPPLÉMENTAIRES (+3)
    // ─────────────────────────────────────────────

    public function testSetMoisGrossesseRetourneStatic(): void
    {
        $reservation = new ReservationClient();
        $result = $reservation->setMoisGrossesse(7);
        $this->assertSame($reservation, $result);
    }

    public function testSetNotesRetourneStatic(): void
    {
        $reservation = new ReservationClient();
        $result = $reservation->setNotes('Note test');
        $this->assertSame($reservation, $result);
    }

    public function testSetTypePatientRetourneStatic(): void
    {
        $reservation = new ReservationClient();
        $result = $reservation->setTypePatient('enceinte');
        $this->assertSame($reservation, $result);
    }
}