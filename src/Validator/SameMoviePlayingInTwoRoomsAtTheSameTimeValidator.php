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
                                        $value->getMovieFormat(), 
                                        $value->getStartTime(), 
                                        $value->getEndTime(),
                                        $value->getId()
                                    );
        


        if ($concurrentlyPlayingShowtime === null) {
            return;
        } 


        $this->context->buildViolation($constraint->message)
            ->setParameter("{{ movieTitle }}", $value->getMovieFormat()->getMovie()->getTitle())
            ->setParameter("{{ roomName }}", $concurrentlyPlayingShowtime->getScreeningRoom()->getName())
            ->setParameter("{{ date }}", $concurrentlyPlayingShowtime->getStartTime()->format("d-m-y"))
            ->setParameter("{{ startsAt }}", $concurrentlyPlayingShowtime->getStartTime()->format("h:i"))
            ->setParameter("{{ endsAt }}", $concurrentlyPlayingShowtime->getEndTime()->format("h:i"))
            ->addViolation();

        

    }


}