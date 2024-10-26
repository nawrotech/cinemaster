<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\MovieScreeningFormat;
use App\Form\MovieScreeningFormatCollectionType;
use App\Form\Type\CinemaType;
use App\Repository\CinemaRepository;
use App\Repository\MovieRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use App\Service\MovieScreeningFormatService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\MovieFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
#[Route("/cinemas")]
class CinemaController extends AbstractController
{
    #[Route('/', name: 'app_cinema')]
    public function index(
        CinemaRepository $cinemaRepository,
    ): Response {

        $cinemas = $cinemaRepository->findOrderedCinemas($this->getUser());

        return $this->render('cinema/index.html.twig', [
            "cinemas" => $cinemas
        ]);
    }


 #[Route('/create/{slug?}', name: 'app_cinema_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ?string $slug = null,
        ?Cinema $cinema = null
    ): Response {   

        if (!$cinema) {
            $cinema =  new Cinema();
            $cinema->setOwner($this->getUser());
        }

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($cinema);
            $em->flush();

            if (!$slug) {
                $this->addFlash("success", "Cinema created!");
            } else {
                $this->addFlash("success", "Cinema updated!");
            }
            
            
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('cinema/create.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{slug?}/add-movies/', name: 'app_cinema_add_movies')]
    public function addMovies(
        MovieRepository $movieRepository,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatRepository $movieScreeningFormatRepository,
        Cinema $cinema
    ): Response {

        $movies = $movieRepository->findAll();

        $screeningFormats = $screeningFormatRepository->findBy([
            "cinema" => $cinema
        ]);

        $screeningFormatIdsForMovie = [];
        foreach ($movies as $movie) {
            $screeningFormatIdsForMovie[$movie->getId()] = $movieScreeningFormatRepository->findScreeningFormatIdsByMovie($movie, $cinema);
        }

        return $this->render('cinema/movie_screening_formats.html.twig', [
            "cinema" => $cinema,
            "movies" => $movies,
            "screeningFormats" => $screeningFormats,
            "screeningFormatIdsForMovie" => $screeningFormatIdsForMovie
        ]);
    }

    #[Route('/{slug?}/update-movie-screening/', name: 'app_cinema_update_movie_screening_format', methods: ["POST"])]
    public function updateMovieScreeningFormat(
        MovieRepository $movieRepository,
        Request $request,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatService $movieScreeningFormatService,
        Cinema $cinema
    ): Response {
        $movie = $movieRepository->find($request->get("movieId"));

        $screeningFormatIds = array_map("intval", $request->get("screeningFormats", []));
        $screeningFormats = $screeningFormatRepository->findBy(["id" => $screeningFormatIds]);
        
        if (count($screeningFormatIds) !== count($screeningFormats)) {
            $this->addFlash("error", "Invalid screening formats!");

            return $this->redirectToRoute("app_cinema_add_movies", [
                "slug" => $cinema->getSlug()
            ]);
        }

        if (!$movie) {
            $this->addFlash("error", "Invalid movie!");

            return $this->redirectToRoute("app_cinema_add_movies", [
                "slug" => $cinema->getSlug()
            ]);
        }

        $movieScreeningFormatService->update($cinema, $movie, $screeningFormatIds);
        $movieScreeningFormatService->create($cinema, $movie, $screeningFormats);
        

        $this->addFlash("success", "Movie screening formats successfully updated for {$movie->getTitle()}!");

        return $this->redirectToRoute("app_cinema_add_movies", [
            "slug" => $cinema->getSlug()
        ]);


    }





  
}
