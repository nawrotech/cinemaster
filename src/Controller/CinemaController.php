<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningFormat;
use App\Form\CinemaType;
use App\Repository\CinemaRepository;
use App\Repository\MovieRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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
            "cinemas" => $cinemas,
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
        Cinema $cinema,
        #[MapQueryParameter] ?string $searchTerm = null,
        #[MapQueryParameter] ?int $page = null
    ): Response {

      
        $adapter = new QueryAdapter($movieRepository->findBySearchTerm($cinema, $searchTerm, true));
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page ?? 1);


        $screeningFormats = $screeningFormatRepository->findBy([
            "cinema" => $cinema
        ]);

        $screeningFormatIdsForMovie = [];
        foreach ($movieRepository->findAll() as $movie) {
            $screeningFormatIdsForMovie[$movie->getId()] = $movieScreeningFormatRepository->findScreeningFormatIdsByMovie($movie, $cinema);
        }

        return $this->render('cinema/movie_screening_formats.html.twig', [
            "cinema" => $cinema,
            "screeningFormats" => $screeningFormats,
            "screeningFormatIdsForMovie" => $screeningFormatIdsForMovie,
            "pager" => $pagerfanta
        ]);
    }

    



  
}
