<?php

namespace App\Tests\Unit;

use App\Entity\Event;
use App\Entity\Requirement;
use PHPUnit\Framework\TestCase;

class RequirementEntityTest extends TestCase
{
    public function testRequirementProperties(): void
    {
        $requirement = new Requirement();
        
        $requirement->setName('Water');

        $this->assertEquals('Water', $requirement->getName());
        $this->assertEquals('Water', (string) $requirement);
    }

    public function testRequirementRelation(): void
    {
        $requirement = new Requirement();
        $event = new Event();

        $requirement->addEvent($event);
        $this->assertCount(1, $requirement->getEvents());
        $this->assertTrue($event->getRequirements()->contains($requirement));

        $requirement->removeEvent($event);
        $this->assertCount(0, $requirement->getEvents());
        $this->assertFalse($event->getRequirements()->contains($requirement));
    }
}
