<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinema/movies")]
class MovieController extends AbstractController
{
    // with filtring using get params
    #[Route('/', name: 'app_movie')]
    public function index(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
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

            // $this->addFlash('success', 'Movie created successfully!');
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
