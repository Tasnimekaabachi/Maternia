<?php

namespace App\Tests\Unit;

use App\Entity\Attendance;
use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AttendanceEntityTest extends TestCase
{
    public function testAttendanceProperties(): void
    {
        $attendance = new Attendance();
        $user = new User();
        $event = new Event();
        $now = new \DateTime();

        $attendance->setUser($user);
        $attendance->setEvent($event);
        $attendance->setCreatedAt($now);

        $this->assertSame($user, $attendance->getUser());
        $this->assertSame($event, $attendance->getEvent());
        $this->assertSame($now, $attendance->getCreatedAt());
    }
}
