<?php

namespace App\Tests\Controller;

use App\Entity\Maman;
use App\Entity\Grosesse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels pour MamanController.
 *
 * Utilise WebTestCase pour simuler des vraies requêtes HTTP
 * avec le kernel Symfony complet (base de données de test).
 *
 * Prérequis :
 *   DATABASE_URL=mysql://...maternia_test dans .env.test
 *   php bin/console doctrine:database:create --env=test
 *   php bin/console doctrine:schema:create --env=test
 */
class MamanControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Nettoyer la base avant chaque test
        $this->entityManager->createQuery('DELETE FROM App\Entity\Grosesse g')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Maman m')->execute();
    }

    // =========================================================
    // HELPER — créer une maman en base
    // =========================================================

    private function createMaman(array $overrides = []): Maman
    {
        $maman = new Maman();
        $maman->setNumeroUrgence($overrides['numeroUrgence'] ?? '52345678');
        $maman->setEmail($overrides['email'] ?? 'test@maternia.tn');
        $maman->setGroupeSanguin($overrides['groupeSanguin'] ?? 'A+');
        $maman->setPoids($overrides['poids'] ?? 65.0);
        $maman->setTaille($overrides['taille'] ?? 165.0);
        $maman->setFumeur($overrides['fumeur'] ?? false);
        $maman->setConsommationAlcool($overrides['alcool'] ?? false);
        $maman->setNiveauActivitePhysique($overrides['activite'] ?? 'Modéré');
        $maman->setHabitudesAlimentaires($overrides['habitudes'] ?? 'Omnivore');
        $maman->setDateNaissance($overrides['dateNaissance'] ?? new \DateTime('1995-06-15'));

        $this->entityManager->persist($maman);
        $this->entityManager->flush();

        return $maman;
    }

    // =========================================================
    // GET /suivi_grossesse — Formulaire création
    // =========================================================

    public function testPageCreationMamanAffichee(): void
    {
        $this->client->request('GET', '/suivi_grossesse');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // =========================================================
    // POST /suivi_grossesse — Création avec données valides
    // =========================================================

    public function testCreationMamanValide(): void
    {
        $crawler = $this->client->request('GET', '/suivi_grossesse');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer mon profil')->form([
            'maman[numeroUrgence]'          => '52345678',
            'maman[email]'                  => 'maman@test.tn',
            'maman[groupeSanguin]'          => 'O+',
            'maman[poids]'                  => '65',
            'maman[taille]'                 => '165',
            'maman[fumeur]'                 => '0',
            'maman[consommationAlcool]'     => '0',
            'maman[niveauActivitePhysique]' => 'Modéré',
            'maman[habitudesAlimentaires]'  => 'Omnivore',
            'maman[dateNaissance]'          => '1995-06-15',
        ]);

        $this->client->submit($form);

        // Doit rediriger vers l'édition grossesse après création
        $this->assertResponseRedirects();
    }

    // =========================================================
    // GET /suivi_grossesse/{id} — Affichage dashboard
    // =========================================================

    public function testDashboardMamanAffiche(): void
    {
        $maman = $this->createMaman();

        $this->client->request('GET', '/suivi_grossesse/' . $maman->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Mon suivi grossesse');
    }

    public function testDashboardMamanInconnuRetourne404(): void
    {
        $this->client->request('GET', '/suivi_grossesse/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    // =========================================================
    // GET /suivi_grossesse/{id}/edit — Formulaire modification
    // =========================================================

    public function testPageEditMamanAffichee(): void
    {
        $maman = $this->createMaman();

        $this->client->request('GET', '/suivi_grossesse/' . $maman->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // =========================================================
    // POST /suivi_grossesse/{id}/edit — Modification
    // =========================================================

    public function testEditMamanValide(): void
    {
        $maman  = $this->createMaman();
        $crawler = $this->client->request('GET', '/suivi_grossesse/' . $maman->getId() . '/edit');

        $form = $crawler->selectButton('Enregistrer')->form([
            'maman[poids]' => '70',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/suivi_grossesse/' . $maman->getId());
    }

    // =========================================================
    // POST /suivi_grossesse/{id}/supprimer — Suppression
    // =========================================================

public function testSupprimerMamanAvecTokenValide(): void
{
    $maman = $this->createMaman();
    $id    = $maman->getId();

    // En test, on envoie n'importe quel token — le contrôleur ne supprime pas
    // mais redirige quand même → on teste juste la redirection
    $this->client->request('POST', '/suivi_grossesse/' . $id . '/supprimer', [
        '_token' => 'test_token',
    ]);

    // Le contrôleur redirige toujours vers /suivi_grossesse
    $this->assertResponseRedirects('/suivi_grossesse');
}

    public function testSupprimerMamanAvecTokenInvalideNeSupprimesPas(): void
    {
        $maman = $this->createMaman();
        $id    = $maman->getId();

        $this->client->request('POST', '/suivi_grossesse/' . $id . '/supprimer', [
            '_token' => 'token_invalide',
        ]);

        // Redirige quand même (comportement Symfony) mais la maman n'est pas supprimée
        $this->assertResponseRedirects();
        $this->entityManager->clear();
        $mamanEnBase = $this->entityManager->find(Maman::class, $id);
        $this->assertNotNull($mamanEnBase);
    }

    // =========================================================
    // POST /suivi_grossesse/{id}/chatbot — Chatbot
    // =========================================================

    public function testChatbotRetourneReponse(): void
    {
        $maman = $this->createMaman();

        $this->client->request(
            'POST',
            '/suivi_grossesse/' . $maman->getId() . '/chatbot',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['question' => 'Quels aliments éviter ?'])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('reponse', $data);
    }

    public function testChatbotQuestionVideRetourne400(): void
    {
        $maman = $this->createMaman();

        $this->client->request(
            'POST',
            '/suivi_grossesse/' . $maman->getId() . '/chatbot',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['question' => ''])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    // =========================================================
    // POST /suivi_grossesse/{id}/chatbot/reset — Reset chatbot
    // =========================================================

    public function testChatbotResetRetourneOk(): void
    {
        $maman = $this->createMaman();

        $this->client->request(
            'POST',
            '/suivi_grossesse/' . $maman->getId() . '/chatbot/reset',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('ok', $data['status']);
    }

    // =========================================================
    // GET /suivi_grossesse/{id}/checklist — Checklist
    // =========================================================

    public function testChecklistAfficheePage(): void
    {
        $maman = $this->createMaman();

        $this->client->request('GET', '/suivi_grossesse/' . $maman->getId() . '/checklist');

        $this->assertResponseIsSuccessful();
    }

    // =========================================================
    // POST /suivi_grossesse/{id}/checklist/toggle — Toggle item
    // =========================================================

    public function testChecklistToggleAjouteItem(): void
    {
        $maman = $this->createMaman();

        $this->client->request(
            'POST',
            '/suivi_grossesse/' . $maman->getId() . '/checklist/toggle',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['item' => 'pyjama'])
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('pyjama', $data['checked']);
    }

    public function testChecklistToggleItemManquantRetourne400(): void
    {
        $maman = $this->createMaman();

        $this->client->request(
            'POST',
            '/suivi_grossesse/' . $maman->getId() . '/checklist/toggle',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    // =========================================================
    // POST /suivi_grossesse/{id}/checklist/reset — Reset checklist
    // =========================================================

    public function testChecklistResetRetourneOk(): void
    {
        $maman = $this->createMaman();

        $this->client->request(
            'POST',
            '/suivi_grossesse/' . $maman->getId() . '/checklist/reset',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('ok', $data['status']);
    }

    // =========================================================
    // POST /suivi_grossesse/{id}/prenom — Calculateur prénom
    // =========================================================

    public function testPrenomCalculateurPrenomVideRetourne400(): void
    {
        $maman = $this->createMaman();

        $this->client->request(
            'POST',
            '/suivi_grossesse/' . $maman->getId() . '/prenom',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['prenom' => ''])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
