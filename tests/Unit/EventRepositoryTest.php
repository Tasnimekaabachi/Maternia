<?php

namespace App\Tests\Unit;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class EventRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new EventRepository($registry);
        
        $this->assertInstanceOf(EventRepository::class, $repository);
    }
}
