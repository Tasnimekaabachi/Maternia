<?php

namespace App\Tests\Entity;

use App\Entity\Maman;
use App\Entity\Grosesse;
use PHPUnit\Framework\TestCase;

class MamanTest extends TestCase
{
    private Maman $maman;

    protected function setUp(): void
    {
        $this->maman = new Maman();
    }

    // =========================================================
    // GETTERS / SETTERS DE BASE
    // =========================================================

    public function testSetAndGetNumeroUrgence(): void
    {
        $this->maman->setNumeroUrgence('52345678');
        $this->assertSame('52345678', $this->maman->getNumeroUrgence());
    }

    public function testSetNumeroUrgenceStripsNonDigits(): void
    {
        $this->maman->setNumeroUrgence('+216 52 345 678');
        $this->assertSame('52345678', $this->maman->getNumeroUrgence());
    }

    public function testSetNumeroUrgenceWithIndicatif216(): void
    {
        $this->maman->setNumeroUrgence('21652345678');
        $this->assertSame('52345678', $this->maman->getNumeroUrgence());
    }

    public function testSetAndGetEmail(): void
    {
        $this->maman->setEmail('test@maternia.tn');
        $this->assertSame('test@maternia.tn', $this->maman->getEmail());
    }

    public function testSetEmailNull(): void
    {
        $this->maman->setEmail(null);
        $this->assertNull($this->maman->getEmail());
    }

    public function testSetAndGetGroupeSanguin(): void
    {
        $this->maman->setGroupeSanguin('A+');
        $this->assertSame('A+', $this->maman->getGroupeSanguin());
    }

    public function testSetAndGetPoids(): void
    {
        $this->maman->setPoids(65.5);
        $this->assertSame(65.5, $this->maman->getPoids());
    }

    public function testSetAndGetTaille(): void
    {
        $this->maman->setTaille(165.0);
        $this->assertSame(165.0, $this->maman->getTaille());
    }

    public function testSetAndGetAllergies(): void
    {
        $this->maman->setAllergies('Pénicilline, arachides');
        $this->assertSame('Pénicilline, arachides', $this->maman->getAllergies());
    }

    public function testSetAndGetAntecedentsMedicaux(): void
    {
        $this->maman->setAntecedentsMedicaux('Diabète gestationnel');
        $this->assertSame('Diabète gestationnel', $this->maman->getAntecedentsMedicaux());
    }

    public function testSetAndGetMaladiesChroniques(): void
    {
        $this->maman->setMaladiesChroniques('Asthme');
        $this->assertSame('Asthme', $this->maman->getMaladiesChroniques());
    }

    public function testSetAndGetMedicamentsActuels(): void
    {
        $this->maman->setMedicamentsActuels('Acide folique');
        $this->assertSame('Acide folique', $this->maman->getMedicamentsActuels());
    }

    public function testSetAndGetFumeur(): void
    {
        $this->maman->setFumeur(true);
        $this->assertTrue($this->maman->isFumeur());

        $this->maman->setFumeur(false);
        $this->assertFalse($this->maman->isFumeur());
    }

    public function testSetAndGetConsommationAlcool(): void
    {
        $this->maman->setConsommationAlcool(true);
        $this->assertTrue($this->maman->isConsommationAlcool());
    }

    public function testSetAndGetNiveauActivitePhysique(): void
    {
        $this->maman->setNiveauActivitePhysique('Modéré');
        $this->assertSame('Modéré', $this->maman->getNiveauActivitePhysique());
    }

    public function testSetAndGetHabitudesAlimentaires(): void
    {
        $this->maman->setHabitudesAlimentaires('Végétarienne');
        $this->assertSame('Végétarienne', $this->maman->getHabitudesAlimentaires());
    }

    public function testSetAndGetDateNaissance(): void
    {
        $date = new \DateTime('1995-06-15');
        $this->maman->setDateNaissance($date);
        $this->assertSame($date, $this->maman->getDateNaissance());
    }

    // =========================================================
    // CALCUL IMC
    // =========================================================

    public function testGetImcNormal(): void
    {
        $this->maman->setPoids(60.0);
        $this->maman->setTaille(165.0);
        // IMC = 60 / (1.65 * 1.65) = 22.0
        $this->assertSame(22.0, $this->maman->getImc());
    }

    public function testGetImcMaigreur(): void
    {
        $this->maman->setPoids(45.0);
        $this->maman->setTaille(165.0);
        // IMC ≈ 16.5 → Maigreur
        $imc = $this->maman->getImc();
        $this->assertNotNull($imc);
        $this->assertLessThan(18.5, $imc);
    }

    public function testGetImcSurpoids(): void
    {
        $this->maman->setPoids(80.0);
        $this->maman->setTaille(165.0);
        // IMC ≈ 29.4 → Surpoids
        $imc = $this->maman->getImc();
        $this->assertNotNull($imc);
        $this->assertGreaterThanOrEqual(25.0, $imc);
        $this->assertLessThan(30.0, $imc);
    }

    public function testGetImcNullWhenPoidsNull(): void
    {
        $this->maman->setPoids(null);
        $this->maman->setTaille(165.0);
        $this->assertNull($this->maman->getImc());
    }

    public function testGetImcNullWhenTailleNull(): void
    {
        $this->maman->setPoids(60.0);
        $this->maman->setTaille(null);
        $this->assertNull($this->maman->getImc());
    }

    public function testGetImcNullWhenTailleZero(): void
    {
        $this->maman->setPoids(60.0);
        $this->maman->setTaille(0.0);
        $this->assertNull($this->maman->getImc());
    }

    // =========================================================
    // CATEGORIE IMC
    // =========================================================

    public function testGetImcCategorieMaigreur(): void
    {
        $this->maman->setPoids(45.0);
        $this->maman->setTaille(165.0);
        $this->assertSame('Maigreur', $this->maman->getImcCategorie());
    }

    public function testGetImcCategorieNormal(): void
    {
        $this->maman->setPoids(60.0);
        $this->maman->setTaille(165.0);
        $this->assertSame('Normal', $this->maman->getImcCategorie());
    }

    public function testGetImcCategorieSurpoids(): void
    {
        $this->maman->setPoids(80.0);
        $this->maman->setTaille(165.0);
        $this->assertSame('Surpoids', $this->maman->getImcCategorie());
    }

    public function testGetImcCategorieObesite(): void
    {
        $this->maman->setPoids(95.0);
        $this->maman->setTaille(165.0);
        $this->assertSame('Obésité', $this->maman->getImcCategorie());
    }

    public function testGetImcCategorieObesiteSevere(): void
    {
        $this->maman->setPoids(120.0);
        $this->maman->setTaille(165.0);
        $this->assertSame('Obésité sévère', $this->maman->getImcCategorie());
    }

    public function testGetImcCategorieNullWhenDataMissing(): void
    {
        $this->assertNull($this->maman->getImcCategorie());
    }

    // =========================================================
    // ALERTE IMC
    // =========================================================

    public function testIsImcAlerteTrue_Maigreur(): void
    {
        // IMC < 17 → alerte
        $this->maman->setPoids(42.0);
        $this->maman->setTaille(165.0);
        $this->assertTrue($this->maman->isImcAlerte());
    }

    public function testIsImcAlerteFalse_Normal(): void
    {
        $this->maman->setPoids(60.0);
        $this->maman->setTaille(165.0);
        $this->assertFalse($this->maman->isImcAlerte());
    }

    public function testIsImcAlerteTrue_ObesiteSevere(): void
    {
        // IMC > 35 → alerte
        $this->maman->setPoids(110.0);
        $this->maman->setTaille(160.0);
        $this->assertTrue($this->maman->isImcAlerte());
    }

    public function testIsImcAlerteFalse_WhenNull(): void
    {
        // Pas de données → pas d'alerte
        $this->assertFalse($this->maman->isImcAlerte());
    }

    // =========================================================
    // AGE
    // =========================================================

    public function testGetAgeCalculCorrect(): void
    {
        $naissance = new \DateTime('-30 years');
        $this->maman->setDateNaissance($naissance);
        $this->assertSame(30, $this->maman->getAge());
    }

    public function testGetAgeDefaultWhenNull(): void
    {
        // Valeur par défaut 28 quand dateNaissance est null
        $this->assertSame(28, $this->maman->getAge());
    }

    // =========================================================
    // RELATION GROSSESSE
    // =========================================================

    public function testAddAndRemoveGrosesse(): void
    {
        $grossesse = new Grosesse();
        $this->maman->addGrosess($grossesse);

        $this->assertCount(1, $this->maman->getGrosesses());
        $this->assertSame($this->maman, $grossesse->getMaman());

        $this->maman->removeGrosess($grossesse);
        $this->assertCount(0, $this->maman->getGrosesses());
    }

    public function testAddGrosessDoesNotDuplicate(): void
    {
        $grossesse = new Grosesse();
        $this->maman->addGrosess($grossesse);
        $this->maman->addGrosess($grossesse); // doublon

        $this->assertCount(1, $this->maman->getGrosesses());
    }

    public function testInitialGrosessesCollectionIsEmpty(): void
    {
        $this->assertCount(0, $this->maman->getGrosesses());
    }
}
