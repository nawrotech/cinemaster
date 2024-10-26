<?php

namespace App\Validator;

use App\Entity\Showtime;
use App\Repository\ShowtimeRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SameMoviePlayingInTwoRoomsAtTheSameTimeValidator  extends ConstraintValidator {

    public function __construct(private ShowtimeRepository $showtimeRepository) {

    }

    public function validate(mixed $value, Constraint $constraint): void {

        if (!$constraint instanceof SameMoviePlayingInTwoRoomsAtTheSameTime) {
            throw new UnexpectedTypeException($constraint, SameMoviePlayingInTwoRoomsAtTheSameTime::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Showtime) {
            throw new UnexpectedValueException($value, Showtime::class);
        }

        $concurrentlyPlayingShowtime = $this->showtimeRepository
                                    ->findOverlappingForMovie(
                                        $value->getCinema(),
                                        $value->getMovieScreeningFormat(), 
                                        $value->getStartsAt(), 
                                        $value->getEndsAt(),
                                        $value->getId()
                                    );
        


        if ($concurrentlyPlayingShowtime === null) {
            return;
        } 


        $this->context->buildViolation($constraint->message)
            ->setParameter("{{ movieTitle }}", $value->getMovieScreeningFormat()->getMovie()->getTitle())
            ->setParameter("{{ roomName }}", $concurrentlyPlayingShowtime->getScreeningRoom()->getName())
            ->setParameter("{{ date }}", $concurrentlyPlayingShowtime->getStartsAt()->format("d-m-y"))
            ->setParameter("{{ startsAt }}", $concurrentlyPlayingShowtime->getStartsAt()->format("h:i"))
            ->setParameter("{{ endsAt }}", $concurrentlyPlayingShowtime->getEndsAt()->format("h:i"))
            ->addViolation();

        

    }


}