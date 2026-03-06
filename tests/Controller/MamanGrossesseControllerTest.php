<?php

namespace App\Tests\Controller;

use App\Entity\Grosesse;
use App\Entity\Maman;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MamanGrossesseControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->entityManager->createQuery('DELETE FROM App\Entity\Grosesse g')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Maman m')->execute();
    }

    private function createMaman(): Maman
    {
        $maman = new Maman();
        $maman->setNumeroUrgence('92345678');
        $maman->setGroupeSanguin('AB+');
        $maman->setPoids(58.0);
        $maman->setTaille(160.0);
        $maman->setFumeur(false);
        $maman->setConsommationAlcool(false);
        $maman->setNiveauActivitePhysique('Modéré');
        $maman->setHabitudesAlimentaires('Végétarienne');
        $maman->setDateNaissance(new \DateTime('1997-01-01'));

        $this->entityManager->persist($maman);
        $this->entityManager->flush();

        return $maman;
    }

    public function testAfficheFormulaireNouvelleGrossesse(): void
    {
        $maman = $this->createMaman();

        $this->client->request('GET', '/suivi_grossesse/' . $maman->getId() . '/grossesse');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testAfficheFormulaireGrossesseExistante(): void
    {
        $maman = $this->createMaman();

        $grossesse = new Grosesse();
        $grossesse->setMaman($maman);
        $grossesse->setConnaitDDR(true);
        $grossesse->setDateDernieresRegles(new \DateTime('-15 weeks'));
        $grossesse->setStatutGrossesse('enCours');
        $grossesse->setTypeGrossesse('simple');
        $grossesse->setRiskLevel('Low');
        $this->entityManager->persist($grossesse);
        $this->entityManager->flush();

        $this->client->request('GET', '/suivi_grossesse/' . $maman->getId() . '/grossesse');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreationGrossesseValide(): void
    {
        $maman   = $this->createMaman();
        $crawler = $this->client->request('GET', '/suivi_grossesse/' . $maman->getId() . '/grossesse');

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form([
            'grosesse[connaitDDR]'              => '1',
            'grosesse[dateDernieresRegles]'     => (new \DateTime('-12 weeks'))->format('Y-m-d'),
            'grosesse[statutGrossesse]'         => 'enCours',
            'grosesse[typeGrossesse]'           => 'simple',
            'grosesse[poidsActuel]'             => '60',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/suivi_grossesse/' . $maman->getId());
    }

    public function testMamanInconnueRetourne404(): void
    {
        $this->client->request('GET', '/suivi_grossesse/99999/grossesse');
        $this->assertResponseStatusCodeSame(404);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
