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
                                        $value->getCinema(),
                                        $value->getScreeningRoom(), 
                                        $value->getStartsAt(), 
                                        $value->getEndsAt(),
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
                $showtime->getMovieScreeningFormat()->getMovie()->getTitle(),
                $showtime->getStartsAt()->format('H:i'),
                $showtime->getEndsAt()->format('H:i')
            );
        }, $overlappingShowtimes);

        return implode('', $formattedOverlaps);
    }
}