<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    public function testEventIndexPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/event/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Événements');
    }

    public function testEventCreation(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/event/new');

        $this->assertResponseIsSuccessful();
        
        $buttonCrawlerNode = $crawler->selectButton('Créer l\'événement');
        $form = $buttonCrawlerNode->form();
        
        $client->submit($form, [
            'event[title]' => 'Test Event',
            'event[description]' => 'This is a test event description',
            'event[location]' => 'Test Location',
            'event[capacity]' => 20,
        ]);

        $this->assertResponseRedirects('/admin/event/');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }
}