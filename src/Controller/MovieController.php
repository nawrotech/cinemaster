<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieFormat;
use App\Entity\MovieScreeningFormat;
use App\Form\MovieFormType;
use App\Form\ScreeningFormatCollectionType;
use App\Repository\MovieRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Service\TmdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/movies")]
class MovieController extends AbstractController
{
    // with filtring using get params
    #[Route('/cinemas/{slug}', name: 'app_movie')]
    public function index(
        MovieRepository $movieRepository,
        TmdbApiService $tmdbApiService,
        Cinema $cinema
        ): Response
    {

        $movies = $tmdbApiService->fetchMovies()["results"];

        $storedTmdbIds = $movieRepository->findTmdbIds($cinema);
        
        return $this->render('movie/index.html.twig', [
            "movies" =>  $movies,
            "cinemaSlug" => $cinema->getSlug(),
            "storedTmdbIds" => $storedTmdbIds
        ]);
    }
 

    #[Route("/formats/{slug}/create", name: "app_movie_create_screening_formats")]
    public function createScreeningFormats(
        EntityManagerInterface $em,
        Cinema $cinema,
        Request $request): Response
    {
        $form = $this->createForm(ScreeningFormatCollectionType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();            

            $this->addFlash("success", "Screening formats have been created!");
            
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('movie/screening_formats_form.html.twig', [
            "form" => $form
        ]);

    }

    #[Route('/create', name: 'app_movie_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($movie);
            $em->flush();

            $this->addFlash('success', 'Movie created successfully!');
            return $this->redirectToRoute('app_movie');
        }

        return $this->render('movie/create.html.twig', [
            "form" => $form
        ]);
    }

    // name for method singular
    // edit for the movie allowed only if there is no showtime planned
    // #[Route('/create', name: 'app_movie_create')]
    // public function create(Request $request): Response
    // {
    //     $movie = new Movie();
    //     $form = $this->createForm(MovieFormType::class, $movie);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         dd($form->getData());

    //         return new Response("all set");
    //     }


    //     return $this->render('movie/index.html.twig', [
    //         "form" => $form
    //     ]);
    // }

    #[Route('/{slug}', name: 'app_cinema_movies_details')]
    public function details(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }




    #[Route('/{slug}/edit', name: 'app_cinema_movies_edit')]
    public function edit(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }
}
