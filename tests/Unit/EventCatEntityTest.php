<?php

namespace App\Tests\Unit;

use App\Entity\Event;
use App\Entity\EventCat;
use PHPUnit\Framework\TestCase;

class EventCatEntityTest extends TestCase
{
    public function testEventCatProperties(): void
    {
        $category = new EventCat();
        
        $category->setName('Sports');
        $category->setDescription('Events related to sports');

        $this->assertEquals('Sports', $category->getName());
        $this->assertEquals('Events related to sports', $category->getDescription());
        $this->assertEquals('Sports', (string) $category);
    }

    public function testEventCatRelation(): void
    {
        $category = new EventCat();
        $event = new Event();

        $category->addEvent($event);
        $this->assertCount(1, $category->getEvents());
        $this->assertSame($category, $event->getEventCat());

        $category->removeEvent($event);
        $this->assertCount(0, $category->getEvents());
        $this->assertNull($event->getEventCat());
    }
}
