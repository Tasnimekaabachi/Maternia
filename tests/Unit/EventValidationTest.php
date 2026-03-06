<?php

namespace App\Tests\Unit;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class EventValidationTest extends TestCase
{
    public function testValidateTimingNonWeeklyFailsWithoutDates(): void
    {
        $event = new Event();
        $event->setIsWeekly(false);

        $context = $this->createMock(ExecutionContextInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        // Expect building violation for startAt and endAt
        $context->expects($this->exactly(2))
            ->method('buildViolation')
            ->with($this->callback(function($message) {
                return str_contains($message, 'début sont obligatoires') || str_contains($message, 'fin sont obligatoires');
            }))
            ->willReturn($violationBuilder);
        
        $violationBuilder->expects($this->exactly(2))
            ->method('atPath')
            ->willReturn($violationBuilder);
        
        $violationBuilder->expects($this->exactly(2))
            ->method('addViolation');

        $event->validateTiming($context, null);
    }

    public function testValidateTimingWeeklyFailsWithoutDayAndTime(): void
    {
        $event = new Event();
        $event->setIsWeekly(true);

        $context = $this->createMock(ExecutionContextInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $context->expects($this->atLeastOnce())
            ->method('buildViolation')
            ->willReturn($violationBuilder);
        
        $violationBuilder->expects($this->atLeastOnce())
            ->method('atPath')
            ->willReturn($violationBuilder);
        
        $violationBuilder->expects($this->atLeastOnce())
            ->method('addViolation');

        $event->validateTiming($context, null);
    }
}
