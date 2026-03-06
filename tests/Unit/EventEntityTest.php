<?php

namespace App\Tests\Unit;

use App\Entity\Event;
use App\Entity\EventCat;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class EventEntityTest extends TestCase
{
    public function testEventProperties(): void
    {
        $event = new Event();
        $now = new \DateTime();

        $event->setTitle('Test Event');
        $event->setDescription('Test Description');
        $event->setStartAt($now);
        $event->setEndAt($now);
        $event->setLocation('Test Location');
        $event->setCapacity(100);
        $event->setIsOutdoor(true);
        $event->setImage('test.jpg');
        $event->setIsWeekly(true);
        $event->setDayOfWeek('Monday');

        $this->assertEquals('Test Event', $event->getTitle());
        $this->assertEquals('Test Description', $event->getDescription());
        $this->assertEquals($now, $event->getStartAt());
        $this->assertEquals($now, $event->getEndAt());
        $this->assertEquals('Test Location', $event->getLocation());
        $this->assertEquals(100, $event->getCapacity());
        $this->assertTrue($event->isOutdoor());
        $this->assertEquals('test.jpg', $event->getImage());
        $this->assertTrue($event->isWeekly());
        $this->assertEquals('Monday', $event->getDayOfWeek());
    }

    public function testEventRelations(): void
    {
        $event = new Event();
        $category = new EventCat();
        $creator = new User();

        $event->setEventCat($category);
        $event->setCreator($creator);

        $this->assertSame($category, $event->getEventCat());
        $this->assertSame($creator, $event->getCreator());
    }
}
