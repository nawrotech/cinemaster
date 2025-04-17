<?php

namespace App\Validator;

use App\Entity\Showtime;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

final class ShowtimeScheduleValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ShowtimeSchedule) {
            throw new UnexpectedTypeException($constraint, ShowtimeSchedule::class);
        }

        /* @var ShowtimeSchedule $constraint */
        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Showtime) {
            throw new UnexpectedValueException($value, Showtime::class);
        }

        $showtimeStartsAt = $value->getStartsAt(); 
        $showtimeEndsAt = $value->getEndsAt(); 
        $showtimeDate = $showtimeStartsAt->format('Y-m-d'); 

        $cinema = $value->getCinema();
        $openTime = $cinema->getOpenTime(); 
        $closeTime = $cinema->getCloseTime(); 

        
        $openDateTime = new \DateTimeImmutable($showtimeDate . ' ' . $openTime->format('H:i:s')); 
        $closeDateTime = new \DateTimeImmutable($showtimeDate . ' ' . $closeTime->format('H:i:s'));  

        // Handle overnight operations
        $isOvernight = $closeTime->format('H:i') < $openTime->format('H:i'); 
        if ($isOvernight) {  
            if ($showtimeEndsAt->format('H:i') < $openTime->format('H:i')) { 
              
                $midnight = new \DateTimeImmutable($showtimeDate . ' 00:00:00');  
                if ($showtimeStartsAt >= $midnight && $showtimeEndsAt <= $closeDateTime) { 
                    return; 
                }
            }
            $closeDateTime = $closeDateTime->modify('+1 day'); 
        }

      
        if ($showtimeStartsAt >= $openDateTime && $showtimeEndsAt <= $closeDateTime) {  
            return;
        }

      
        $operatingHours = $openTime->format('H:i') . ' - ' . $closeTime->format('H:i');
        $showtimeDuration = $showtimeStartsAt->format('H:i') . ' - ' . $showtimeEndsAt->format('H:i');

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ cinemaOperatingHours }}', $operatingHours)
            ->setParameter('{{ showtimeDuration }}', $showtimeDuration)
            ->addViolation();
    }
}
