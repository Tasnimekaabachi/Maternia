<?php

namespace App\Tests\Entity;

use App\Entity\Consultation;
use App\Entity\ConsultationCreneau;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires — Entité Consultation
 * Maternia — Sprint Web 2
 */
class ConsultationTest extends TestCase
{
    // ─────────────────────────────────────────────
    // 1. GETTERS / SETTERS DE BASE
    // ─────────────────────────────────────────────

    public function testSetAndGetCategorie(): void
    {
        $consultation = new Consultation();
        $consultation->setCategorie('Gynécologie');
        $this->assertSame('Gynécologie', $consultation->getCategorie());
    }

    public function testSetAndGetDescription(): void
    {
        $consultation = new Consultation();
        $consultation->setDescription('Consultation de suivi prénatal.');
        $this->assertSame('Consultation de suivi prénatal.', $consultation->getDescription());
    }

    public function testSetDescriptionNull(): void
    {
        $consultation = new Consultation();
        $consultation->setDescription(null);
        $this->assertNull($consultation->getDescription());
    }

    public function testSetAndGetPour(): void
    {
        $consultation = new Consultation();
        $consultation->setPour('Femme enceinte');
        $this->assertSame('Femme enceinte', $consultation->getPour());
    }

    public function testSetAndGetImage(): void
    {
        $consultation = new Consultation();
        $consultation->setImage('image.png');
        $this->assertSame('image.png', $consultation->getImage());
    }

    public function testSetImageNull(): void
    {
        $consultation = new Consultation();
        $consultation->setImage(null);
        $this->assertNull($consultation->getImage());
    }

    public function testSetAndGetIcon(): void
    {
        $consultation = new Consultation();
        $consultation->setIcon('fa-heart');
        $this->assertSame('fa-heart', $consultation->getIcon());
    }

    public function testSetIconNull(): void
    {
        $consultation = new Consultation();
        $consultation->setIcon(null);
        $this->assertNull($consultation->getIcon());
    }

    // ─────────────────────────────────────────────
    // 2. STATUT (booléen)
    // ─────────────────────────────────────────────

    public function testSetStatutTrue(): void
    {
        $consultation = new Consultation();
        $consultation->setStatut(true);
        $this->assertTrue($consultation->isStatut());
    }

    public function testSetStatutFalse(): void
    {
        $consultation = new Consultation();
        $consultation->setStatut(false);
        $this->assertFalse($consultation->isStatut());
    }

    // ─────────────────────────────────────────────
    // 3. ORDRE D'AFFICHAGE
    // ─────────────────────────────────────────────

    public function testSetAndGetOrdreAffichage(): void
    {
        $consultation = new Consultation();
        $consultation->setOrdreAffichage(3);
        $this->assertSame(3, $consultation->getOrdreAffichage());
    }

    public function testSetOrdreAffichageNull(): void
    {
        $consultation = new Consultation();
        $consultation->setOrdreAffichage(null);
        $this->assertNull($consultation->getOrdreAffichage());
    }

    public function testOrdreAffichageZero(): void
    {
        $consultation = new Consultation();
        $consultation->setOrdreAffichage(0);
        $this->assertSame(0, $consultation->getOrdreAffichage());
    }

    // ─────────────────────────────────────────────
    // 4. DATES createdAt / updatedAt
    // ─────────────────────────────────────────────

    public function testSetAndGetCreatedAt(): void
    {
        $consultation = new Consultation();
        $date = new \DateTime('2026-01-15 10:00:00');
        $consultation->setCreatedAt($date);
        $this->assertSame($date, $consultation->getCreatedAt());
    }

    public function testSetCreatedAtNull(): void
    {
        $consultation = new Consultation();
        $consultation->setCreatedAt(null);
        $this->assertNull($consultation->getCreatedAt());
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $consultation = new Consultation();
        $date = new \DateTime('2026-03-01 14:30:00');
        $consultation->setUpdatedAt($date);
        $this->assertSame($date, $consultation->getUpdatedAt());
    }

    public function testSetUpdatedAtNull(): void
    {
        $consultation = new Consultation();
        $consultation->setUpdatedAt(null);
        $this->assertNull($consultation->getUpdatedAt());
    }

    // ─────────────────────────────────────────────
    // 5. ID (null par défaut avant persistance)
    // ─────────────────────────────────────────────

    public function testIdNullParDefaut(): void
    {
        $consultation = new Consultation();
        $this->assertNull($consultation->getId());
    }

    // ─────────────────────────────────────────────
    // 6. COLLECTION ConsultationCreneaux
    // ─────────────────────────────────────────────

    public function testCollectionCreneauxVideParDefaut(): void
    {
        $consultation = new Consultation();
        $this->assertCount(0, $consultation->getConsultationCreneaus());
    }

    public function testAddConsultationCreneau(): void
    {
        $consultation = new Consultation();
        $creneau = new ConsultationCreneau();

        $consultation->addConsultationCreneau($creneau);

        $this->assertCount(1, $consultation->getConsultationCreneaus());
        $this->assertTrue($consultation->getConsultationCreneaus()->contains($creneau));
    }

    public function testAddMemeCreneauDeuxFois(): void
    {
        $consultation = new Consultation();
        $creneau = new ConsultationCreneau();

        $consultation->addConsultationCreneau($creneau);
        $consultation->addConsultationCreneau($creneau); // doit être ignoré

        $this->assertCount(1, $consultation->getConsultationCreneaus());
    }

    public function testAddCreneauLieConsultation(): void
    {
        $consultation = new Consultation();
        $creneau = new ConsultationCreneau();

        $consultation->addConsultationCreneau($creneau);

        $this->assertSame($consultation, $creneau->getConsultation());
    }

    public function testRemoveConsultationCreneau(): void
    {
        $consultation = new Consultation();
        $creneau = new ConsultationCreneau();

        $consultation->addConsultationCreneau($creneau);
        $consultation->removeConsultationCreneau($creneau);

        $this->assertCount(0, $consultation->getConsultationCreneaus());
    }

    public function testAddPlusieursCreneaux(): void
    {
        $consultation = new Consultation();

        for ($i = 0; $i < 5; $i++) {
            $creneau = new ConsultationCreneau();
            $consultation->addConsultationCreneau($creneau);
        }

        $this->assertCount(5, $consultation->getConsultationCreneaus());
    }

    // ─────────────────────────────────────────────
    // 7. FLUENT INTERFACE (retourne static)
    // ─────────────────────────────────────────────

    public function testSetCategorieRetourneStatic(): void
    {
        $consultation = new Consultation();
        $result = $consultation->setCategorie('Cardio');
        $this->assertSame($consultation, $result);
    }

    public function testSetPourRetourneStatic(): void
    {
        $consultation = new Consultation();
        $result = $consultation->setPour('Tous');
        $this->assertSame($consultation, $result);
    }

    public function testSetStatutRetourneStatic(): void
    {
        $consultation = new Consultation();
        $result = $consultation->setStatut(true);
        $this->assertSame($consultation, $result);
    }

    // ─────────────────────────────────────────────
    // 8. CAS LIMITES & VALEURS EXTRÊMES
    // ─────────────────────────────────────────────

    public function testCategorieStringVide(): void
    {
        $consultation = new Consultation();
        $consultation->setCategorie('');
        $this->assertSame('', $consultation->getCategorie());
    }

    public function testCategorieAvecCaracteresSpeciaux(): void
    {
        $consultation = new Consultation();
        $consultation->setCategorie('Périnatalité & Obstétrique');
        $this->assertSame('Périnatalité & Obstétrique', $consultation->getCategorie());
    }

    public function testOrdreAffichageNegatif(): void
    {
        $consultation = new Consultation();
        $consultation->setOrdreAffichage(-1);
        $this->assertSame(-1, $consultation->getOrdreAffichage());
    }

    public function testDescriptionLongue(): void
    {
        $consultation = new Consultation();
        $texte = str_repeat('a', 5000);
        $consultation->setDescription($texte);
        $this->assertSame($texte, $consultation->getDescription());
    }

    // ─────────────────────────────────────────────
    // TESTS SUPPLÉMENTAIRES (+3)
    // ─────────────────────────────────────────────

    public function testRemoveCreneauNonPresentNeChangeRien(): void
    {
        $consultation = new Consultation();
        $creneau = new ConsultationCreneau();
        $consultation->removeConsultationCreneau($creneau);
        $this->assertCount(0, $consultation->getConsultationCreneaus());
    }

    public function testSetDescriptionRetourneStatic(): void
    {
        $consultation = new Consultation();
        $result = $consultation->setDescription('Test');
        $this->assertSame($consultation, $result);
    }

    public function testSetOrdreAffichageRetourneStatic(): void
    {
        $consultation = new Consultation();
        $result = $consultation->setOrdreAffichage(1);
        $this->assertSame($consultation, $result);
    }
}