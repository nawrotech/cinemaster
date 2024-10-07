<?php

namespace App\Validator;

use App\Entity\Showtime;
use App\Repository\ShowtimeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class OverlappingShowtimeInSameScreeningRoomValidator extends ConstraintValidator {

    public function __construct(private ShowtimeRepository $showtimeRepository) {

    }

    public function validate(mixed $value, Constraint $constraint): void {

        if (!$constraint instanceof OverlappingShowtimeInSameScreeningRoom) {
            throw new UnexpectedTypeException($constraint, OverlappingShowtimeInSameScreeningRoom::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Showtime) {
            throw new UnexpectedValueException($value, Showtime::class);
        }

        $overlappingShowtimes = $this->showtimeRepository
                                    ->findOverlappingForRoom(
                                        $value->getScreeningRoom(), 
                                        $value->getStartTime(), 
                                        $value->getEndTime(),
                                        $value->getId()
                                    );

        if (empty($overlappingShowtimes)) {
            return;
        }


        $overlapsMessage = $this->formatOverlappingShowtimes($overlappingShowtimes);
        $this->context->buildViolation($constraint->message)
                        ->setParameter("{{ overlaps }}", $overlapsMessage)
                        ->addViolation();

    }

    private function formatOverlappingShowtimes(array $overlappingShowtimes): string
    {
        $formattedOverlaps = array_map(function (Showtime $showtime) {
            return sprintf(
                "\n- %s (%s - %s)",
                $showtime->getMovieFormat()->getMovie()->getTitle(),
                $showtime->getStartTime()->format('H:i'),
                $showtime->getEndTime()->format('H:i')
            );
        }, $overlappingShowtimes);

        return implode('', $formattedOverlaps);
    }
}