<?php

namespace App\Tests\Controller;

use App\Entity\Grosesse;
use App\Entity\Maman;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GrosesseControllerTest extends WebTestCase
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
        $maman->setNumeroUrgence('52345678');
        $maman->setEmail('test@maternia.tn');
        $maman->setGroupeSanguin('B+');
        $maman->setPoids(60.0);
        $maman->setTaille(163.0);
        $maman->setFumeur(false);
        $maman->setConsommationAlcool(false);
        $maman->setNiveauActivitePhysique('Léger');
        $maman->setHabitudesAlimentaires('Omnivore');
        $maman->setDateNaissance(new \DateTime('1993-03-10'));

        $this->entityManager->persist($maman);
        $this->entityManager->flush();

        return $maman;
    }

    private function createGrosesse(Maman $maman): Grosesse
    {
        $grosesse = new Grosesse();
        $grosesse->setMaman($maman);
        $grosesse->setConnaitDDR(true);
        $grosesse->setDateDernieresRegles(new \DateTime('-12 weeks'));
        $grosesse->setStatutGrossesse('enCours');
        $grosesse->setTypeGrossesse('simple');
        $grosesse->setPoidsActuel(62.0);
        $grosesse->setRiskLevel('Low');

        $this->entityManager->persist($grosesse);
        $this->entityManager->flush();

        return $grosesse;
    }

    public function testIndexAfficheListeGrossesses(): void
    {
        $this->client->request('GET', '/grosesse');
        $this->assertResponseIsSuccessful();
    }

    public function testPageNouvelleGrossesseAffichee(): void
    {
        $this->client->request('GET', '/grosesse/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testAffichageGrossesseExistante(): void
    {
        $maman    = $this->createMaman();
        $grosesse = $this->createGrosesse($maman);

        $this->client->request('GET', '/grosesse/' . $grosesse->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testAffichageGrossesseInconnueRetourne404(): void
    {
        $this->client->request('GET', '/grosesse/99999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPageEditGrossesseAffichee(): void
    {
        $maman    = $this->createMaman();
        $grosesse = $this->createGrosesse($maman);

        $this->client->request('GET', '/grosesse/' . $grosesse->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

 public function testSupprimerGrossesseAvecTokenValide(): void
{
    $maman    = $this->createMaman();
    $grosesse = $this->createGrosesse($maman);
    $id       = $grosesse->getId();

    $this->client->request('POST', '/grosesse/' . $id, [
        '_token' => 'test_token',
    ]);

    $this->assertResponseRedirects('/grosesse');
}

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
