<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use Doctrine\ORM\EntityManagerInterface;

class MovieScreeningFormatService {

    public function __construct(
        private MovieScreeningFormatRepository $movieScreeningFormatRepository,
        private ScreeningFormatRepository $screeningFormatRepository,
        private EntityManagerInterface $em
        )
    {
    }

    /**
     * @var $screeningFormatIds int[]
     */
    public function update(Cinema $cinema, Movie $movie, array $screeningFormatIds) {

        $existingScreeningFormatIdsForMovieAtCinema = $this->movieScreeningFormatRepository
                                                  ->findScreeningFormatIdsForMovieAtCinema($movie, $cinema);

        $removedScreeningFormatIds = array_diff($existingScreeningFormatIdsForMovieAtCinema, $screeningFormatIds);

        $removedScreeningFormats = $this->movieScreeningFormatRepository
                                        ->findByScreeningFormatIds($removedScreeningFormatIds, $movie);

        foreach ($removedScreeningFormats as $removedScreeningFormat) {
                            $this->em->remove($removedScreeningFormat);
                            $this->em->flush();
                    }

    }

    /**
     * @var $screeningFormats ScreeningFormat[]
     */
    public function create(Cinema $cinema, Movie $movie, array $screeningFormatIds) {

        $screeningFormats = $this->screeningFormatRepository->findBy(["id" => $screeningFormatIds]);

        foreach ($screeningFormats as $screeningFormat) {
            if ($this->movieScreeningFormatRepository->findBy([
                "cinema" => $cinema,
                "movie" => $movie,
                "screeningFormat" => $screeningFormat

            ])) {
                continue;
            }

            $msf = new MovieScreeningFormat();
            $msf->setCinema($cinema);
            $msf->setMovie($movie);
            $msf->setScreeningFormat($screeningFormat);

            $this->em->persist($msf);
            $this->em->flush();

        }

    } 

}