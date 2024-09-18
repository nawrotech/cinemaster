<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinema")]
class CinemaController extends AbstractController
{
    // view created cinemas
    #[Route('/', name: 'app_cinema_create')]
    public function index(): Response
    {
        return $this->render('cinema/index.html.twig', [
            'controller_name' => 'CinemaController',
        ]);
    }

    // isGrantedAdmin
    #[Route('/create', name: 'app_cinema_create')]
    public function create(): Response
    {
        return $this->render('cinema/index.html.twig', [
            'controller_name' => 'CinemaController',
        ]);
    }

    // isGrantedAdmin, slug with cinema name
    #[Route('/{slug}/edit', name: 'app_cinema_edit')]
    public function edit(): Response
    {
        return $this->render('cinema/index.html.twig', [
            'controller_name' => 'CinemaController',
        ]);
    }
}
