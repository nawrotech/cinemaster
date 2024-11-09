<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningFormat;
use App\Repository\MovieRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class MovieScreeningFormatController extends AbstractController
{

    #[Route('/cinemas/{slug}/cinema-screening-formats', name: 'app_movie_screening_format_cinema_screening_formats')]
    public function cinemaScreeningFormats(
        ScreeningFormatRepository $screeningFormatRepository,
        Cinema $cinema,
        #[MapQueryParameter()] string $screeningFormat = ""
    ): Response {

        $screeningFormats = $screeningFormatRepository
                                    ->findScreeningFormatsBySearchedTermForCinema($cinema,
                                     $screeningFormat);

        if (empty($screeningFormats)) {
            return new Response("<div class=\"list-group-item\" >No results :(</div>");
        }

        $displayScreeningFormats = array_map(function (ScreeningFormat $screeningFormat) {
            return 
                "<li class=\"list-group-item\" role=\"option\" data-autocomplete-value=\"{$screeningFormat->getId()}\">
                    {$screeningFormat->getDisplayScreeningFormat()}
                </li>";
        }, $screeningFormats);


        $htmlFragment = (implode("", $displayScreeningFormats));
        return new Response($htmlFragment);

    }


    #[Route("/movie-screening-formats/movies/{id}", name: "app_movie_screening_format_for_movie")]
    public function movieScreeningFormatsForMovie(
        Movie $movie,
        MovieScreeningFormatRepository $movieScreeningFormatRepository
    ) {

        $screeningFormats = $movieScreeningFormatRepository
                            ->findScreeningFormatsForMovie($movie);

        $apiResponseScreeningFormats = array_map(function (MovieScreeningFormat $msf) {
            return [
                "id" => $msf->getId(),
                "movieScreeningFormatName" => $msf->getScreeningFormat()->getDisplayScreeningFormat(),
            ];
        }, $screeningFormats);

        return $this->json($apiResponseScreeningFormats);
    }

    #[Route("/movie-screening-formats/movies/{movieId}/screening-formats/{id?}", name: "app_movie_screening_format_create", requirements: ["id" => "[^/]*"], methods: ["POST"])]    
    public function addMovieScreeningFormat(
        EntityManagerInterface $em,
        MovieScreeningFormatRepository $movieScreeningFormatRepository,
        #[MapEntity(mapping: ["movieId" => "id"])] Movie $movie,
        #[MapEntity(mapping: ["id" => "id"])] ?ScreeningFormat $screeningFormat = null,
    ) {

        $movieScreeningFormatExists = $movieScreeningFormatRepository->findBy([
            "movie" => $movie,
            "screeningFormat" => $screeningFormat
        ]);

        if ($movieScreeningFormatExists) {
            return $this->json([
                "status" => "conflict",
                "message" => "A movie screening format with the specified parameters already exists."
            ], 409);
        }

        $movieScreeningFormat = new MovieScreeningFormat();
        $movieScreeningFormat->setCinema($movie->getCinema());
        $movieScreeningFormat->setMovie($movie);
        $movieScreeningFormat->setScreeningFormat($screeningFormat);

        $em->persist($movieScreeningFormat);
        $em->flush();

        return $this->json(null, 201);
    }



    #[Route("/movie-screening-formats/{id?}", name: "app_movie_screening_format_delete", requirements: ["id" => "[^/]*"], methods: ["DELETE"])]    
    public function deleteMovieScreeningFormat(
        EntityManagerInterface $em,
        ?MovieScreeningFormat $movieScreeningFormat = null,
        
    ) {
        $em->remove($movieScreeningFormat);
        $em->flush();

        return $this->json(null, 204);
    }

 

    #[Route('/movie/screening/format', name: 'app_movie_screening_format')]
    public function index(): Response
    {
        return $this->render('movie_screening_format/index.html.twig', [
            'controller_name' => 'MovieScreeningFormatController',
        ]);
    }
}
