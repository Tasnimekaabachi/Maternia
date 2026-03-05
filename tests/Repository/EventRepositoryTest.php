<?php

namespace App\Tests\Repository;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EventRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private EventRepository $eventRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->eventRepository = $this->entityManager
            ->getRepository(Event::class);
    }

    public function testFindUpcomingEvents(): void
    {
        $events = $this->eventRepository->findUpcomingEvents(5);
        $this->assertIsArray($events);
    }

    public function testFindWithSearchAndSort(): void
    {
        $events = $this->eventRepository->findWithSearchAndSort(
            'test',  // search term
            'title', // sort by
            'ASC',   // sort order
            null,    // category id
            null,    // status
            null     // organizer
        );
        
        $this->assertIsArray($events);
    }
}