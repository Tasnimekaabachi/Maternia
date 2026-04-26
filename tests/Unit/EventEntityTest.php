<?php

namespace App\Tests\Entity;

use App\Entity\Attendance;
use App\Entity\Event;
use App\Entity\EventCat;
use App\Entity\Requirement;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires — Entité Event
 * Maternia — Sprint Web 2
 */
class EventTest extends TestCase
{
    // ─────────────────────────────────────────────
    // 1. ID & CONSTRUCTEUR
    // ─────────────────────────────────────────────

    public function testIdNullParDefaut(): void
    {
        $event = new Event();
        $this->assertNull($event->getId());
    }

    public function testIsWeeklyFalseParDefaut(): void
    {
        $event = new Event();
        $this->assertFalse($event->isWeekly());
    }

    public function testIsOutdoorFalseParDefaut(): void
    {
        $event = new Event();
        $this->assertFalse($event->isOutdoor());
    }

    public function testCollectionsInitialiseesParDefaut(): void
    {
        $event = new Event();
        $this->assertCount(0, $event->getAttendances());
        $this->assertCount(0, $event->getRequirements());
    }

    // ─────────────────────────────────────────────
    // 2. TITRE
    // ─────────────────────────────────────────────

    public function testSetAndGetTitle(): void
    {
        $event = new Event();
        $event->setTitle('Yoga Prénatal');
        $this->assertSame('Yoga Prénatal', $event->getTitle());
    }

    public function testTitleNullParDefaut(): void
    {
        $event = new Event();
        $this->assertNull($event->getTitle());
    }

    public function testTitleAvecCaracteresSpeciaux(): void
    {
        $event = new Event();
        $event->setTitle('Atelier & Bien-être Maman');
        $this->assertSame('Atelier & Bien-être Maman', $event->getTitle());
    }

    public function testSetTitleRetourneStatic(): void
    {
        $event = new Event();
        $result = $event->setTitle('Test');
        $this->assertSame($event, $result);
    }

    // ─────────────────────────────────────────────
    // 3. DESCRIPTION
    // ─────────────────────────────────────────────

    public function testSetAndGetDescription(): void
    {
        $event = new Event();
        $event->setDescription('Séance de yoga adaptée aux femmes enceintes.');
        $this->assertSame('Séance de yoga adaptée aux femmes enceintes.', $event->getDescription());
    }

    public function testDescriptionNullParDefaut(): void
    {
        $event = new Event();
        $this->assertNull($event->getDescription());
    }

    public function testDescriptionLongue(): void
    {
        $event = new Event();
        $texte = str_repeat('Description. ', 300);
        $event->setDescription($texte);
        $this->assertSame($texte, $event->getDescription());
    }

    public function testSetDescriptionRetourneStatic(): void
    {
        $event = new Event();
        $result = $event->setDescription('Test description longue');
        $this->assertSame($event, $result);
    }

    // ─────────────────────────────────────────────
    // 4. DATES startAt / endAt
    // ─────────────────────────────────────────────

    public function testSetAndGetStartAt(): void
    {
        $event = new Event();
        $date = new \DateTime('2026-04-15 10:00:00');
        $event->setStartAt($date);
        $this->assertSame($date, $event->getStartAt());
    }

    public function testSetStartAtNull(): void
    {
        $event = new Event();
        $event->setStartAt(null);
        $this->assertNull($event->getStartAt());
    }

    public function testSetAndGetEndAt(): void
    {
        $event = new Event();
        $date = new \DateTime('2026-04-15 12:00:00');
        $event->setEndAt($date);
        $this->assertSame($date, $event->getEndAt());
    }

    public function testSetEndAtNull(): void
    {
        $event = new Event();
        $event->setEndAt(null);
        $this->assertNull($event->getEndAt());
    }

    public function testEndAtApresStartAt(): void
    {
        $event = new Event();
        $start = new \DateTime('2026-04-15 10:00:00');
        $end   = new \DateTime('2026-04-15 12:00:00');
        $event->setStartAt($start);
        $event->setEndAt($end);
        $this->assertGreaterThan($event->getStartAt(), $event->getEndAt());
    }

    // ─────────────────────────────────────────────
    // 5. LOCATION
    // ─────────────────────────────────────────────

    public function testSetAndGetLocation(): void
    {
        $event = new Event();
        $event->setLocation('Tunis, Centre Maternia');
        $this->assertSame('Tunis, Centre Maternia', $event->getLocation());
    }

    public function testSetLocationRetourneStatic(): void
    {
        $event = new Event();
        $result = $event->setLocation('Tunis');
        $this->assertSame($event, $result);
    }

    // ─────────────────────────────────────────────
    // 6. IMAGE
    // ─────────────────────────────────────────────

    public function testSetAndGetImage(): void
    {
        $event = new Event();
        $event->setImage('event_yoga.jpg');
        $this->assertSame('event_yoga.jpg', $event->getImage());
    }

    public function testSetImageNull(): void
    {
        $event = new Event();
        $event->setImage(null);
        $this->assertNull($event->getImage());
    }

    // ─────────────────────────────────────────────
    // 7. IS WEEKLY
    // ─────────────────────────────────────────────

    public function testSetIsWeeklyTrue(): void
    {
        $event = new Event();
        $event->setIsWeekly(true);
        $this->assertTrue($event->isWeekly());
    }

    public function testSetIsWeeklyFalse(): void
    {
        $event = new Event();
        $event->setIsWeekly(false);
        $this->assertFalse($event->isWeekly());
    }

    public function testSetIsWeeklyRetourneStatic(): void
    {
        $event = new Event();
        $result = $event->setIsWeekly(true);
        $this->assertSame($event, $result);
    }

    // ─────────────────────────────────────────────
    // 8. IS OUTDOOR
    // ─────────────────────────────────────────────

    public function testSetIsOutdoorTrue(): void
    {
        $event = new Event();
        $event->setIsOutdoor(true);
        $this->assertTrue($event->isOutdoor());
    }

    public function testSetIsOutdoorFalse(): void
    {
        $event = new Event();
        $event->setIsOutdoor(false);
        $this->assertFalse($event->isOutdoor());
    }

    // ─────────────────────────────────────────────
    // 9. CAPACITY
    // ─────────────────────────────────────────────

    public function testSetAndGetCapacity(): void
    {
        $event = new Event();
        $event->setCapacity(50);
        $this->assertSame(50, $event->getCapacity());
    }

    public function testCapacityNullParDefaut(): void
    {
        $event = new Event();
        $this->assertNull($event->getCapacity());
    }

    public function testCapacityMinimum2(): void
    {
        $event = new Event();
        $event->setCapacity(2);
        $this->assertGreaterThan(1, $event->getCapacity());
    }

    public function testSetCapacityNull(): void
    {
        $event = new Event();
        $event->setCapacity(null);
        $this->assertNull($event->getCapacity());
    }

    // ─────────────────────────────────────────────
    // 10. DAY OF WEEK
    // ─────────────────────────────────────────────

    public function testSetAndGetDayOfWeek(): void
    {
        $event = new Event();
        $event->setDayOfWeek('Monday');
        $this->assertSame('Monday', $event->getDayOfWeek());
    }

    public function testSetDayOfWeekNull(): void
    {
        $event = new Event();
        $event->setDayOfWeek(null);
        $this->assertNull($event->getDayOfWeek());
    }

    public function testDayOfWeekJoursSemaine(): void
    {
        $jours = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $event = new Event();

        foreach ($jours as $jour) {
            $event->setDayOfWeek($jour);
            $this->assertSame($jour, $event->getDayOfWeek());
        }
    }

    // ─────────────────────────────────────────────
    // 11. START TIME / END TIME
    // ─────────────────────────────────────────────

    public function testSetAndGetStartTime(): void
    {
        $event = new Event();
        $heure = new \DateTime('09:00:00');
        $event->setStartTime($heure);
        $this->assertSame($heure, $event->getStartTime());
    }

    public function testSetStartTimeNull(): void
    {
        $event = new Event();
        $event->setStartTime(null);
        $this->assertNull($event->getStartTime());
    }

    public function testSetAndGetEndTime(): void
    {
        $event = new Event();
        $heure = new \DateTime('11:00:00');
        $event->setEndTime($heure);
        $this->assertSame($heure, $event->getEndTime());
    }

    public function testSetEndTimeNull(): void
    {
        $event = new Event();
        $event->setEndTime(null);
        $this->assertNull($event->getEndTime());
    }

    // ─────────────────────────────────────────────
    // 12. RELATION EventCat (ManyToOne)
    // ─────────────────────────────────────────────

    public function testSetAndGetEventCat(): void
    {
        $event = new Event();
        $cat   = new EventCat();
        $cat->setName('Yoga Prénatal');

        $event->setEventCat($cat);

        $this->assertSame($cat, $event->getEventCat());
    }

    public function testSetEventCatNull(): void
    {
        $event = new Event();
        $event->setEventCat(null);
        $this->assertNull($event->getEventCat());
    }

    // ─────────────────────────────────────────────
    // 13. COLLECTION Attendances
    // ─────────────────────────────────────────────

    public function testAddAttendance(): void
    {
        $event      = new Event();
        $attendance = new Attendance();

        $event->addAttendance($attendance);

        $this->assertCount(1, $event->getAttendances());
        $this->assertTrue($event->getAttendances()->contains($attendance));
    }

    public function testAddMemeAttendanceDeuxFois(): void
    {
        $event      = new Event();
        $attendance = new Attendance();

        $event->addAttendance($attendance);
        $event->addAttendance($attendance);

        $this->assertCount(1, $event->getAttendances());
    }

    public function testRemoveAttendance(): void
    {
        $event      = new Event();
        $attendance = new Attendance();

        $event->addAttendance($attendance);
        $event->removeAttendance($attendance);

        $this->assertCount(0, $event->getAttendances());
    }

    // ─────────────────────────────────────────────
    // 14. COLLECTION Requirements
    // ─────────────────────────────────────────────

    public function testAddRequirement(): void
    {
        $event       = new Event();
        $requirement = new Requirement();

        $event->addRequirement($requirement);

        $this->assertCount(1, $event->getRequirements());
        $this->assertTrue($event->getRequirements()->contains($requirement));
    }

    public function testAddMemeRequirementDeuxFois(): void
    {
        $event       = new Event();
        $requirement = new Requirement();

        $event->addRequirement($requirement);
        $event->addRequirement($requirement);

        $this->assertCount(1, $event->getRequirements());
    }

    public function testRemoveRequirement(): void
    {
        $event       = new Event();
        $requirement = new Requirement();

        $event->addRequirement($requirement);
        $event->removeRequirement($requirement);

        $this->assertCount(0, $event->getRequirements());
    }

    // ─────────────────────────────────────────────
    // 15. CREATOR (ManyToOne User)
    // ─────────────────────────────────────────────

    public function testSetAndGetCreator(): void
    {
        $event = new Event();
        $user  = new User();

        $event->setCreator($user);

        $this->assertSame($user, $event->getCreator());
    }

    public function testSetCreatorNull(): void
    {
        $event = new Event();
        $event->setCreator(null);
        $this->assertNull($event->getCreator());
    }
}
