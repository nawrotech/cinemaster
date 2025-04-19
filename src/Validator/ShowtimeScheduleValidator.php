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

        $isOvernightCinema = $closeTime->format('H:i') < $openTime->format('H:i');
        
        if ($isOvernightCinema) {
            // CASE 1: Show starts and ends after midnight (early morning)
            if ($showtimeStartsAt->format('H:i') < $openTime->format('H:i') &&
                $showtimeEndsAt->format('H:i') <= $closeTime->format('H:i')) {
                return;
            }
             
            // CASE 2: Show starts in evening and ends after midnight
            if ($showtimeStartsAt->format('H:i') >= $openTime->format('H:i') &&
                $showtimeEndsAt->format('H:i') < $showtimeStartsAt->format('H:i') &&
                $showtimeEndsAt->format('H:i') <= $closeTime->format('H:i')) {
                return; 
            }
            
            // CASE 3: Show is entirely in evening part (before midnight)
            if ($showtimeStartsAt->format('H:i') >= $openTime->format('H:i') &&
                $showtimeEndsAt->format('H:i') > $showtimeStartsAt->format('H:i') &&
                $showtimeEndsAt->format('H:i') <= '23:59') {
                return; 
            }
        } else {
            // Show must be entirely within operating hours
            if ($showtimeStartsAt >= $openDateTime && 
                $showtimeEndsAt <= $closeDateTime &&
                $showtimeEndsAt->format('H:i') > $showtimeStartsAt->format('H:i')) {
                return; 
            }
        }

        $operatingHours = $openTime->format('H:i') . ' - ' . $closeTime->format('H:i');
        $showtimeDuration = $showtimeStartsAt->format('H:i') . ' - ' . $showtimeEndsAt->format('H:i');

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ cinemaOperatingHours }}', $operatingHours)
            ->setParameter('{{ showtimeDuration }}', $showtimeDuration)
            ->addViolation();
    }
}
