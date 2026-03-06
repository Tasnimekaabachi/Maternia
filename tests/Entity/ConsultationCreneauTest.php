<?php

namespace App\Tests\Entity;

use App\Entity\Consultation;
use App\Entity\ConsultationCreneau;
use App\Entity\ReservationClient;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires — Entité ConsultationCreneau
 * Maternia — Sprint Web 2
 */
class ConsultationCreneauTest extends TestCase
{
    // ─────────────────────────────────────────────
    // 1. GETTERS / SETTERS MÉDECIN
    // ─────────────────────────────────────────────

    public function testSetAndGetNomMedecin(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setNomMedecin('Dr. Amira Ben Salem');
        $this->assertSame('Dr. Amira Ben Salem', $creneau->getNomMedecin());
    }

    public function testSetAndGetPhotoMedecin(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setPhotoMedecin('photo_medecin.jpg');
        $this->assertSame('photo_medecin.jpg', $creneau->getPhotoMedecin());
    }

    public function testSetPhotoMedecinNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setPhotoMedecin(null);
        $this->assertNull($creneau->getPhotoMedecin());
    }

    public function testSetAndGetDescriptionMedecin(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setDescriptionMedecin('Spécialiste en suivi de grossesse.');
        $this->assertSame('Spécialiste en suivi de grossesse.', $creneau->getDescriptionMedecin());
    }

    public function testSetDescriptionMedecinNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setDescriptionMedecin(null);
        $this->assertNull($creneau->getDescriptionMedecin());
    }

    public function testSetAndGetSpecialiteMedecin(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setSpecialiteMedecin('Gynécologie-Obstétrique');
        $this->assertSame('Gynécologie-Obstétrique', $creneau->getSpecialiteMedecin());
    }

    public function testSetSpecialiteMedecinNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setSpecialiteMedecin(null);
        $this->assertNull($creneau->getSpecialiteMedecin());
    }

    // ─────────────────────────────────────────────
    // 2. DATES (dateDebut / dateFin)
    // ─────────────────────────────────────────────

    public function testSetAndGetDateDebut(): void
    {
        $creneau = new ConsultationCreneau();
        $date = new \DateTime('2026-04-10 09:00:00');
        $creneau->setDateDebut($date);
        $this->assertSame($date, $creneau->getDateDebut());
    }

    public function testSetAndGetDateFin(): void
    {
        $creneau = new ConsultationCreneau();
        $date = new \DateTime('2026-04-10 10:00:00');
        $creneau->setDateFin($date);
        $this->assertSame($date, $creneau->getDateFin());
    }

    // ─────────────────────────────────────────────
    // 3. SYNCHRONISATION AUTOMATIQUE DES DATES
    // setDateDebut → synchronise jour + heureDebut
    // ─────────────────────────────────────────────

    public function testSetDateDebutSynchroniseJour(): void
    {
        $creneau = new ConsultationCreneau();
        $date = new \DateTime('2026-04-10 09:00:00');
        $creneau->setDateDebut($date);

        $this->assertNotNull($creneau->getJour());
        $this->assertSame('2026-04-10', $creneau->getJour()->format('Y-m-d'));
    }

    public function testSetDateDebutSynchroniseHeureDebut(): void
    {
        $creneau = new ConsultationCreneau();
        $date = new \DateTime('2026-04-10 09:30:00');
        $creneau->setDateDebut($date);

        $this->assertNotNull($creneau->getHeureDebut());
        $this->assertSame('09:30:00', $creneau->getHeureDebut()->format('H:i:s'));
    }

    public function testSetDateFinSynchroniseHeureFin(): void
    {
        $creneau = new ConsultationCreneau();
        $date = new \DateTime('2026-04-10 10:30:00');
        $creneau->setDateFin($date);

        $this->assertNotNull($creneau->getHeureFin());
        $this->assertSame('10:30:00', $creneau->getHeureFin()->format('H:i:s'));
    }

    // ─────────────────────────────────────────────
    // 4. SETTERS MANUELS : jour, heureDebut, heureFin
    // ─────────────────────────────────────────────

    public function testSetAndGetJour(): void
    {
        $creneau = new ConsultationCreneau();
        $jour = new \DateTime('2026-05-20');
        $creneau->setJour($jour);
        $this->assertSame($jour, $creneau->getJour());
    }

    public function testSetJourNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setJour(null);
        $this->assertNull($creneau->getJour());
    }

    public function testSetAndGetHeureDebut(): void
    {
        $creneau = new ConsultationCreneau();
        $heure = new \DateTime('08:00:00');
        $creneau->setHeureDebut($heure);
        $this->assertSame($heure, $creneau->getHeureDebut());
    }

    public function testSetHeureDebutNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setHeureDebut(null);
        $this->assertNull($creneau->getHeureDebut());
    }

    public function testSetAndGetHeureFin(): void
    {
        $creneau = new ConsultationCreneau();
        $heure = new \DateTime('09:00:00');
        $creneau->setHeureFin($heure);
        $this->assertSame($heure, $creneau->getHeureFin());
    }

    public function testSetHeureFin(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setHeureFin(null);
        $this->assertNull($creneau->getHeureFin());
    }

    // ─────────────────────────────────────────────
    // 5. syncDates — Cohérence complète
    // setJour + setHeureDebut → dateDebut reconstruite
    // ─────────────────────────────────────────────

    public function testSyncDatesAvecJourEtHeureDebut(): void
    {
        $creneau = new ConsultationCreneau();
        $jour = new \DateTime('2026-06-15');
        $heure = new \DateTime('10:00:00');

        $creneau->setJour($jour);
        $creneau->setHeureDebut($heure);

        $this->assertNotNull($creneau->getDateDebut());
        $this->assertSame('2026-06-15 10:00:00', $creneau->getDateDebut()->format('Y-m-d H:i:s'));
    }

    public function testSyncDatesAvecJourEtHeureFin(): void
    {
        $creneau = new ConsultationCreneau();
        $jour = new \DateTime('2026-06-15');
        $heure = new \DateTime('11:00:00');

        $creneau->setJour($jour);
        $creneau->setHeureFin($heure);

        $this->assertNotNull($creneau->getDateFin());
        $this->assertSame('2026-06-15 11:00:00', $creneau->getDateFin()->format('Y-m-d H:i:s'));
    }

    // ─────────────────────────────────────────────
    // 6. STATUT RÉSERVATION
    // ─────────────────────────────────────────────

    public function testSetAndGetStatutReservationDisponible(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setStatutReservation('disponible');
        $this->assertSame('disponible', $creneau->getStatutReservation());
    }

    public function testSetAndGetStatutReservationReserve(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setStatutReservation('réservé');
        $this->assertSame('réservé', $creneau->getStatutReservation());
    }

    public function testSetAndGetStatutReservationAnnule(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setStatutReservation('annulé');
        $this->assertSame('annulé', $creneau->getStatutReservation());
    }

    // ─────────────────────────────────────────────
    // 7. DURÉE ET NOMBRE DE PLACES (valeurs par défaut)
    // ─────────────────────────────────────────────

    public function testDureeMinutesDefaut(): void
    {
        $creneau = new ConsultationCreneau();
        $this->assertSame(30, $creneau->getDureeMinutes());
    }

    public function testNombrePlacesDefaut(): void
    {
        $creneau = new ConsultationCreneau();
        $this->assertSame(1, $creneau->getNombrePlaces());
    }

    public function testSetAndGetDureeMinutes(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setDureeMinutes(45);
        $this->assertSame(45, $creneau->getDureeMinutes());
    }

    public function testSetDureeMinutesNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setDureeMinutes(null);
        $this->assertNull($creneau->getDureeMinutes());
    }

    public function testSetAndGetNombrePlaces(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setNombrePlaces(5);
        $this->assertSame(5, $creneau->getNombrePlaces());
    }

    public function testSetNombrePlacesNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setNombrePlaces(null);
        $this->assertNull($creneau->getNombrePlaces());
    }

    // ─────────────────────────────────────────────
    // 8. DATES createdAt / updatedAt
    // ─────────────────────────────────────────────

    public function testSetAndGetCreatedAt(): void
    {
        $creneau = new ConsultationCreneau();
        $date = new \DateTime('2026-01-01 00:00:00');
        $creneau->setCreatedAt($date);
        $this->assertSame($date, $creneau->getCreatedAt());
    }

    public function testSetCreatedAtNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setCreatedAt(null);
        $this->assertNull($creneau->getCreatedAt());
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $creneau = new ConsultationCreneau();
        $date = new \DateTime('2026-03-05 12:00:00');
        $creneau->setUpdatedAt($date);
        $this->assertSame($date, $creneau->getUpdatedAt());
    }

    public function testSetUpdatedAtNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setUpdatedAt(null);
        $this->assertNull($creneau->getUpdatedAt());
    }

    // ─────────────────────────────────────────────
    // 9. RELATION — Consultation (ManyToOne)
    // ─────────────────────────────────────────────

    public function testIdNullParDefaut(): void
    {
        $creneau = new ConsultationCreneau();
        $this->assertNull($creneau->getId());
    }

    public function testSetAndGetConsultation(): void
    {
        $creneau = new ConsultationCreneau();
        $consultation = new Consultation();
        $consultation->setCategorie('Pédiatrie');

        $creneau->setConsultation($consultation);
        $this->assertSame($consultation, $creneau->getConsultation());
    }

    public function testSetConsultationNull(): void
    {
        $creneau = new ConsultationCreneau();
        $creneau->setConsultation(null);
        $this->assertNull($creneau->getConsultation());
    }

    // ─────────────────────────────────────────────
    // 10. FLUENT INTERFACE (retourne static)
    // ─────────────────────────────────────────────

    public function testSetNomMedecinRetourneStatic(): void
    {
        $creneau = new ConsultationCreneau();
        $result = $creneau->setNomMedecin('Dr. Test');
        $this->assertSame($creneau, $result);
    }

    public function testSetStatutReservationRetourneStatic(): void
    {
        $creneau = new ConsultationCreneau();
        $result = $creneau->setStatutReservation('disponible');
        $this->assertSame($creneau, $result);
    }

    public function testSetDureeMinutesRetourneStatic(): void
    {
        $creneau = new ConsultationCreneau();
        $result = $creneau->setDureeMinutes(60);
        $this->assertSame($creneau, $result);
    }

    // ─────────────────────────────────────────────
    // TESTS SUPPLÉMENTAIRES (+3)
    // ─────────────────────────────────────────────

    public function testSetConsultationRetourneStatic(): void
    {
        $creneau = new ConsultationCreneau();
        $result = $creneau->setConsultation(null);
        $this->assertSame($creneau, $result);
    }

    public function testSetNombrePlacesRetourneStatic(): void
    {
        $creneau = new ConsultationCreneau();
        $result = $creneau->setNombrePlaces(3);
        $this->assertSame($creneau, $result);
    }

    public function testSetJourRetourneStatic(): void
    {
        $creneau = new ConsultationCreneau();
        $result = $creneau->setJour(null);
        $this->assertSame($creneau, $result);
    }
}