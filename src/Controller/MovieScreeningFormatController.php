<?php

namespace App\Controller;

use App\Dto\MovieScreeningFormatDto;
use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningFormat;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class MovieScreeningFormatController extends AbstractController
{

    #[Route('/cinemas/{slug}/cinema-screening-formats', name: 'app_movie_screening_format_cinema_screening_formats')]
    public function cinemaScreeningFormats(
        ScreeningFormatRepository $screeningFormatRepository,
        Cinema $cinema,
        #[MapQueryParameter()] string $screeningFormatTerm = ""
    ): Response {

        $screeningFormats = $screeningFormatRepository
                                    ->findScreeningFormatsBySearchedTermForCinema($cinema,
                                     $screeningFormatTerm);

        if (empty($screeningFormats)) {
            return $this->render('movie_screening_format/_screening_format_no_results_list_item.html.twig');
        }

        return $this->render('movie_screening_format/_screening_format_list_items.html.twig', [
            'screeningFormats' => $screeningFormats
        ]);
    }


    #[Route("/movie-screening-formats/movies/{id}", name: "app_movie_screening_format_for_movie")]
    public function movieScreeningFormatsForMovie(
        Movie $movie,
        MovieScreeningFormatRepository $movieScreeningFormatRepository
    ): Response {
        $screeningFormats = $movieScreeningFormatRepository
                            ->findScreeningFormatsForMovie($movie);

        $apiResponseScreeningFormats = array_map(function (MovieScreeningFormat $msf) {
            return MovieScreeningFormatDto::fromEntity($msf);
        }, $screeningFormats);

        return $this->json($apiResponseScreeningFormats);
    }

    #[Route("/movie-screening-formats/movies/{movieId}/screening-formats/{id?}", name: "app_movie_screening_format_create", methods: ["POST"])]    
    public function addMovieScreeningFormat(
        EntityManagerInterface $em,
        MovieScreeningFormatRepository $movieScreeningFormatRepository,
        #[MapEntity(mapping: ["movieId" => "id"])] Movie $movie,
        #[MapEntity(mapping: ["id" => "id"])] ScreeningFormat $screeningFormat
    ): Response {

        $movieScreeningFormatExists = $movieScreeningFormatRepository->findBy([
            "movie" => $movie,
            "screeningFormat" => $screeningFormat
        ]);

        if ($movieScreeningFormatExists) {
            return $this->json([
                "status" => "conflict",
                "message" => "A movie screening format with the specified parameters already exists."
            ], Response::HTTP_CONFLICT);
        }

        $movieScreeningFormat = new MovieScreeningFormat();
        $movieScreeningFormat->setCinema($movie->getCinema());
        $movieScreeningFormat->setMovie($movie);
        $movieScreeningFormat->setScreeningFormat($screeningFormat);

        $em->persist($movieScreeningFormat);
        $em->flush();

        return $this->json(null, Response::HTTP_CREATED);
    }



    #[Route("/movie-screening-formats/{id?}", name: "app_movie_screening_format_delete", methods: ["DELETE"])]    
    public function deleteMovieScreeningFormat(
        EntityManagerInterface $em,
        MovieScreeningFormat $movieScreeningFormat
    ): Response {
        $em->remove($movieScreeningFormat);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }


}
