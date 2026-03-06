<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use App\Entity\EventCat;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires — Entité EventCat
 * Maternia — Sprint Web 2
 */
class EventCatTest extends TestCase
{
    // ─────────────────────────────────────────────
    // 1. ID
    // ─────────────────────────────────────────────

    public function testIdNullParDefaut(): void
    {
        $cat = new EventCat();
        $this->assertNull($cat->getId());
    }

    // ─────────────────────────────────────────────
    // 2. NAME
    // ─────────────────────────────────────────────

    public function testSetAndGetName(): void
    {
        $cat = new EventCat();
        $cat->setName('Yoga Prénatal');
        $this->assertSame('Yoga Prénatal', $cat->getName());
    }

    public function testNameNullParDefaut(): void
    {
        $cat = new EventCat();
        $this->assertNull($cat->getName());
    }

    public function testNameAvecCaracteresSpeciaux(): void
    {
        $cat = new EventCat();
        $cat->setName('Bien-être & Maternité');
        $this->assertSame('Bien-être & Maternité', $cat->getName());
    }

    public function testNameMinimum4Caracteres(): void
    {
        $cat = new EventCat();
        $cat->setName('Yoga');
        $this->assertSame(4, strlen($cat->getName()));
    }

    public function testSetNameRetourneStatic(): void
    {
        $cat = new EventCat();
        $result = $cat->setName('Test');
        $this->assertSame($cat, $result);
    }

    // ─────────────────────────────────────────────
    // 3. DESCRIPTION
    // ─────────────────────────────────────────────

    public function testSetAndGetDescription(): void
    {
        $cat = new EventCat();
        $cat->setDescription('Catégorie dédiée aux activités de bien-être.');
        $this->assertSame('Catégorie dédiée aux activités de bien-être.', $cat->getDescription());
    }

    public function testDescriptionNullParDefaut(): void
    {
        $cat = new EventCat();
        $this->assertNull($cat->getDescription());
    }

    public function testSetDescriptionNull(): void
    {
        $cat = new EventCat();
        $cat->setDescription(null);
        $this->assertNull($cat->getDescription());
    }

    public function testDescriptionLongue(): void
    {
        $cat = new EventCat();
        $texte = str_repeat('Description longue. ', 100);
        $cat->setDescription($texte);
        $this->assertSame($texte, $cat->getDescription());
    }

    public function testSetDescriptionRetourneStatic(): void
    {
        $cat = new EventCat();
        $result = $cat->setDescription('Test description');
        $this->assertSame($cat, $result);
    }

    // ─────────────────────────────────────────────
    // 4. COLLECTION EVENTS
    // ─────────────────────────────────────────────

    public function testCollectionEventsVideParDefaut(): void
    {
        $cat = new EventCat();
        $this->assertCount(0, $cat->getEvents());
    }

    public function testAddEvent(): void
    {
        $cat = new EventCat();
        $event = new Event();

        $cat->addEvent($event);

        $this->assertCount(1, $cat->getEvents());
        $this->assertTrue($cat->getEvents()->contains($event));
    }

    public function testAddMemeEventDeuxFois(): void
    {
        $cat = new EventCat();
        $event = new Event();

        $cat->addEvent($event);
        $cat->addEvent($event);

        $this->assertCount(1, $cat->getEvents());
    }

    public function testAddEventLieEventCat(): void
    {
        $cat = new EventCat();
        $event = new Event();

        $cat->addEvent($event);

        $this->assertSame($cat, $event->getEventCat());
    }

    public function testRemoveEvent(): void
    {
        $cat = new EventCat();
        $event = new Event();

        $cat->addEvent($event);
        $cat->removeEvent($event);

        $this->assertCount(0, $cat->getEvents());
    }

    public function testAddPlusieursEvents(): void
    {
        $cat = new EventCat();

        for ($i = 0; $i < 5; $i++) {
            $event = new Event();
            $cat->addEvent($event);
        }

        $this->assertCount(5, $cat->getEvents());
    }

    public function testRemoveEventNonPresentNeChangeRien(): void
    {
        $cat = new EventCat();
        $event = new Event();

        $cat->removeEvent($event);

        $this->assertCount(0, $cat->getEvents());
    }

    // ─────────────────────────────────────────────
    // 5. __toString
    // ─────────────────────────────────────────────

    public function testToStringRetourneNom(): void
    {
        $cat = new EventCat();
        $cat->setName('Activités Bébé');
        $this->assertSame('Activités Bébé', (string) $cat);
    }

    public function testToStringSansNomRetourneStringVide(): void
    {
        $cat = new EventCat();
        $this->assertSame('', (string) $cat);
    }
}
