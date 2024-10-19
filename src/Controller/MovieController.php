<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\MovieMovieType;
use App\Form\MovieFormType;
use App\Repository\MovieMovieTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/movies")]
class MovieController extends AbstractController
{
    // with filtring using get params
    #[Route('/', name: 'app_movie')]
    public function index(MovieMovieTypeRepository $movieFormatRepository): Response
    {
        return $this->render('movie/index.html.twig', [
            "movies" =>  $movieFormatRepository->findMovieWithFormats()
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

        // if ($form->isSubmitted()) {
        //     // dd($form);

        //     $submittedToken = $request->getPayload()->get('token');
        //     dd($submittedToken);

        // }

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($movie);

            foreach($form->get("movieTypes")->getData() as $movieType) {
                $movieFormat = new MovieMovieType();
                $movieFormat->setMovie($movie);
                $movieFormat->setMovieType($movieType);

                $em->persist($movieFormat);
            }

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
