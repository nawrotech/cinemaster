<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Form\CinemaType;
use App\Form\CinemaVisualFormatCollectionType;
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
#[Route("/admin/cinemas")]
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

    #[Route('/create', name: 'app_cinema_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
    ): Response {   

        $cinema =  new Cinema();
        $cinema->setOwner($this->getUser());
        
        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($cinema);
            $em->flush();

            if ($request->get("addVisualFormats")) {
               
                $this->addFlash("success", "Info about your cinema saved, continue by adding visual formats available in your cinema!");
                return $this->redirectToRoute("app_cinema_visual_formats", [
                    "slug" => $cinema->getSlug()
                ]);
            }

            $this->addFlash("success", "Info about your cinema saved!");
            return $this->redirectToRoute("app_cinema");
        
        }

        return $this->render('cinema/create.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{slug}/add-visual-formats', name: 'app_cinema_visual_formats')]
    public function addVisualFormats(
        Request $request,
        Cinema $cinema,
        EntityManagerInterface $em,
    ): Response {   

        if (!$cinema->getVisualFormats()->isEmpty()) {
            return $this->redirectToRoute("app_cinema");
        }
        
        $form = $this->createForm(CinemaVisualFormatCollectionType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cinema);
            $em->flush();

            $this->addFlash("success", "Cinema created!");
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('cinema/visual_formats_collection_form.html.twig', [
            "form" => $form
        ]);
    }


    #[Route('/{slug}', name: 'app_cinema_details')]
    public function cinemaDetails(Cinema $cinema) {

        return $this->render("cinema/cinema_details.html.twig", [
            "cinema" => $cinema
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
