<?php

namespace App\Tests\Entity;

use App\Entity\Grosesse;
use App\Entity\Maman;
use PHPUnit\Framework\TestCase;

class GrosesseTest extends TestCase
{
    private Grosesse $grosesse;

    protected function setUp(): void
    {
        $this->grosesse = new Grosesse();
    }

    // =========================================================
    // GETTERS / SETTERS DE BASE
    // =========================================================

    public function testSetAndGetStatutGrossesse(): void
    {
        $this->grosesse->setStatutGrossesse('enCours');
        $this->assertSame('enCours', $this->grosesse->getStatutGrossesse());
    }

    public function testSetAndGetTypeGrossesse(): void
    {
        $this->grosesse->setTypeGrossesse('simple');
        $this->assertSame('simple', $this->grosesse->getTypeGrossesse());
    }

    public function testSetAndGetPoidsActuel(): void
    {
        $this->grosesse->setPoidsActuel(68.5);
        $this->assertSame(68.5, $this->grosesse->getPoidsActuel());
    }

    public function testSetAndGetPoidsActuelNull(): void
    {
        $this->grosesse->setPoidsActuel(null);
        $this->assertNull($this->grosesse->getPoidsActuel());
    }

    public function testSetAndGetSymptomes(): void
    {
        $this->grosesse->setSymptomes('Nausées légères');
        $this->assertSame('Nausées légères', $this->grosesse->getSymptomes());
    }

    public function testSetAndGetNomBebe(): void
    {
        $this->grosesse->setNomBebe('Yasmine');
        $this->assertSame('Yasmine', $this->grosesse->getNomBebe());
    }

    public function testSetAndGetSexeBebe(): void
    {
        $this->grosesse->setSexeBebe('F');
        $this->assertSame('F', $this->grosesse->getSexeBebe());
    }

    public function testSetAndGetPoidsNaissance(): void
    {
        $this->grosesse->setPoidsNaissance(3.2);
        $this->assertSame(3.2, $this->grosesse->getPoidsNaissance());
    }

    public function testSetAndGetTailleNaissance(): void
    {
        $this->grosesse->setTailleNaissance(50.0);
        $this->assertSame(50.0, $this->grosesse->getTailleNaissance());
    }

    public function testSetAndGetEtatNaissance(): void
    {
        $this->grosesse->setEtatNaissance('sain');
        $this->assertSame('sain', $this->grosesse->getEtatNaissance());
    }

    public function testSetAndGetRiskLevel(): void
    {
        $this->grosesse->setRiskLevel('High');
        $this->assertSame('High', $this->grosesse->getRiskLevel());
    }

    public function testSetAndGetNombreBebes(): void
    {
        $this->grosesse->setNombreBebes(2);
        $this->assertSame(2, $this->grosesse->getNombreBebes());
    }

    public function testSetAndGetConnaitDDR(): void
    {
        $this->grosesse->setConnaitDDR(true);
        $this->assertTrue($this->grosesse->isConnaitDDR());

        $this->grosesse->setConnaitDDR(false);
        $this->assertFalse($this->grosesse->isConnaitDDR());
    }

    public function testSetAndGetMaman(): void
    {
        $maman = new Maman();
        $this->grosesse->setMaman($maman);
        $this->assertSame($maman, $this->grosesse->getMaman());
    }

    // =========================================================
    // SYMPTOMES BOOLÉENS
    // =========================================================

    public function testSymptomesBooleens(): void
    {
        $this->grosesse->setNausee(true);
        $this->assertTrue($this->grosesse->isNausee());

        $this->grosesse->setVomissement(true);
        $this->assertTrue($this->grosesse->isVomissement());

        $this->grosesse->setSaignement(true);
        $this->assertTrue($this->grosesse->isSaignement());

        $this->grosesse->setFievre(true);
        $this->assertTrue($this->grosesse->isFievre());

        $this->grosesse->setDouleurAbdominale(true);
        $this->assertTrue($this->grosesse->isDouleurAbdominale());

        $this->grosesse->setFatigue(true);
        $this->assertTrue($this->grosesse->isFatigue());

        $this->grosesse->setVertiges(true);
        $this->assertTrue($this->grosesse->isVertiges());
    }

    public function testSymptomesDefaultFalse(): void
    {
        $g = new Grosesse();
        $this->assertFalse($g->isNausee());
        $this->assertFalse($g->isVomissement());
        $this->assertFalse($g->isSaignement());
        $this->assertFalse($g->isFievre());
        $this->assertFalse($g->isDouleurAbdominale());
        $this->assertFalse($g->isFatigue());
        $this->assertFalse($g->isVertiges());
    }

    // =========================================================
    // LISTE SYMPTOMES
    // =========================================================

    public function testGetSymptomesListEmpty(): void
    {
        $this->assertSame([], $this->grosesse->getSymptomesList());
    }

    public function testGetSymptomesListWithOneSymptome(): void
    {
        $this->grosesse->setNausee(true);
        $this->assertContains('Nausée', $this->grosesse->getSymptomesList());
        $this->assertCount(1, $this->grosesse->getSymptomesList());
    }

    public function testGetSymptomesListWithMultipleSymptomes(): void
    {
        $this->grosesse->setNausee(true);
        $this->grosesse->setFievre(true);
        $this->grosesse->setFatigue(true);

        $list = $this->grosesse->getSymptomesList();
        $this->assertCount(3, $list);
        $this->assertContains('Nausée', $list);
        $this->assertContains('Fièvre', $list);
        $this->assertContains('Fatigue', $list);
    }

    public function testGetSymptomesListAllSymptomes(): void
    {
        $this->grosesse->setNausee(true);
        $this->grosesse->setVomissement(true);
        $this->grosesse->setSaignement(true);
        $this->grosesse->setFievre(true);
        $this->grosesse->setDouleurAbdominale(true);
        $this->grosesse->setFatigue(true);
        $this->grosesse->setVertiges(true);

        $this->assertCount(7, $this->grosesse->getSymptomesList());
    }

    // =========================================================
    // DATE ACCOUCHEMENT PRÉVUE
    // =========================================================

    public function testGetDateAccouchementPrevueAvecDDR(): void
    {
        $ddr = new \DateTime('-10 weeks');
        $this->grosesse->setConnaitDDR(true);
        $this->grosesse->setDateDernieresRegles($ddr);

        $datePrevue = $this->grosesse->getDateAccouchementPrevue();
        $this->assertNotNull($datePrevue);

        // Doit être DDR + 280 jours
        $attendu = \DateTimeImmutable::createFromInterface($ddr)->modify('+280 days');
        $this->assertSame($attendu->format('Y-m-d'), $datePrevue->format('Y-m-d'));
    }

    public function testGetDateAccouchementPrevueSansConnaitDDR(): void
    {
        $dateDebut = new \DateTime('-10 weeks');
        $this->grosesse->setConnaitDDR(false);
        $this->grosesse->setDateDebutGrossesse($dateDebut);

        $datePrevue = $this->grosesse->getDateAccouchementPrevue();
        $this->assertNotNull($datePrevue);

        $attendu = \DateTimeImmutable::createFromInterface($dateDebut)->modify('+280 days');
        $this->assertSame($attendu->format('Y-m-d'), $datePrevue->format('Y-m-d'));
    }

    public function testGetDateAccouchementPrevueNullSansDate(): void
    {
        $this->assertNull($this->grosesse->getDateAccouchementPrevue());
    }

    // =========================================================
    // SEMAINE ACTUELLE
    // =========================================================

public function testGetSemaineActuelleAvecDDR(): void
{
    $ddr = new \DateTime('-10 weeks');
    $this->grosesse->setConnaitDDR(true);
    $this->grosesse->setDateDernieresRegles($ddr);

    $semaine = $this->grosesse->getSemaineActuelle();
    $this->assertNotNull($semaine);
    $this->assertGreaterThanOrEqual(10, $semaine);
    $this->assertLessThanOrEqual(12, $semaine);
}

    public function testGetSemaineActuelleSansDate(): void
    {
        $this->assertNull($this->grosesse->getSemaineActuelle());
    }

    public function testGetSemaineActuelleMinimum1(): void
    {
        // DDR = aujourd'hui → semaine 1 (minimum)
        $this->grosesse->setConnaitDDR(true);
        $this->grosesse->setDateDernieresRegles(new \DateTime('today'));

        $semaine = $this->grosesse->getSemaineActuelle();
        $this->assertSame(1, $semaine);
    }

    // =========================================================
    // TRIMESTRE ACTUEL
    // =========================================================

    public function testGetTrimestreActuel_T1(): void
    {
        // Semaine 8 → Trimestre 1
        $ddr = new \DateTime('-7 weeks');
        $this->grosesse->setConnaitDDR(true);
        $this->grosesse->setDateDernieresRegles($ddr);

        $this->assertSame(1, $this->grosesse->getTrimestreActuel());
    }

    public function testGetTrimestreActuel_T2(): void
    {
        // Semaine 20 → Trimestre 2
        $ddr = new \DateTime('-19 weeks');
        $this->grosesse->setConnaitDDR(true);
        $this->grosesse->setDateDernieresRegles($ddr);

        $this->assertSame(2, $this->grosesse->getTrimestreActuel());
    }

    public function testGetTrimestreActuel_T3(): void
    {
        // Semaine 30 → Trimestre 3
        $ddr = new \DateTime('-29 weeks');
        $this->grosesse->setConnaitDDR(true);
        $this->grosesse->setDateDernieresRegles($ddr);

        $this->assertSame(3, $this->grosesse->getTrimestreActuel());
    }

    public function testGetTrimestreActuelNullSansDate(): void
    {
        $this->assertNull($this->grosesse->getTrimestreActuel());
    }

    // =========================================================
    // BEBES JSON
    // =========================================================

    public function testSetAndGetBebesArray(): void
    {
        $bebes = [
            ['nom' => 'Adam', 'sexe' => 'M', 'poids' => 3.2],
            ['nom' => 'Lina', 'sexe' => 'F', 'poids' => 3.0],
        ];

        $this->grosesse->setBebes($bebes);
        $result = $this->grosesse->getBebes();

        $this->assertCount(2, $result);
        $this->assertSame('Adam', $result[0]['nom']);
        $this->assertSame('Lina', $result[1]['nom']);
    }

    public function testGetBebesReturnEmptyArrayWhenNull(): void
    {
        $this->assertSame([], $this->grosesse->getBebes());
    }

    public function testSetBebesNullClearsData(): void
    {
        $this->grosesse->setBebes([['nom' => 'Test']]);
        $this->grosesse->setBebes(null);
        $this->assertSame([], $this->grosesse->getBebes());
    }

    public function testSetBebesEmptyArrayClearsData(): void
    {
        $this->grosesse->setBebes([['nom' => 'Test']]);
        $this->grosesse->setBebes([]);
        $this->assertSame([], $this->grosesse->getBebes());
    }
}
