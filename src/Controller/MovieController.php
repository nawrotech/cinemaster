<?php

namespace App\Controller;

use App\Adapter\TmdbAdapter;
use App\Entity\Cinema;
use App\Entity\Movie;
use App\Factory\TmdbAdapterFactory;
use App\Form\MovieFormType;
use App\Form\ScreeningFormatCollectionType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/movies")]
class MovieController extends AbstractController
{
    #[Route('/cinemas/{slug}', name: 'app_movie')]
    public function index(
        MovieRepository $movieRepository,
        TmdbAdapterFactory $tmdbAdapterFactory,
        Cinema $cinema,
        #[MapQueryParameter()]
        ? string $q,
        #[MapQueryParameter()]
        ?int $page = 1,
        ): Response
    {
        $endpoint = $q ? "search/movie" : "movie/popular";
        $params = $q ? ["query" => $q] : [];

        $adapter = $tmdbAdapterFactory->create($endpoint, $params);

        $pagerfanta = new Pagerfanta($adapter);

        $currentPage = max(1, $page);
        $pagerfanta->setCurrentPage($currentPage);
        $pagerfanta->setMaxPerPage(TmdbAdapter::MAX_PER_PAGE);

        $storedTmdbIds = $movieRepository->findTmdbIds($cinema);
        
        return $this->render('movie/index.html.twig', [
            "cinemaSlug" => $cinema->getSlug(),
            "storedTmdbIds" => $storedTmdbIds,
            "pager" => $pagerfanta
            
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
