<?php

namespace App\Controller;

use App\Entity\MovieType;
use App\Repository\MovieTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MovieTypeController extends AbstractController
{
    // #[Route('/movie/type', name: 'app_movie_type')]
    // public function index(EntityManagerInterface $em): Response
    // {
    //     $dubbing2d = new MovieType();
    //     $dubbing3d = new MovieType();
    //     $subs2d = new MovieType();
    //     $subs3d = new MovieType();

    //     $dubbing2d->setAudioVersion("dubbing");
    //     $dubbing2d->setVisualVersion("2D");

    //     $dubbing3d->setAudioVersion("dubbing");
    //     $dubbing3d->setVisualVersion("3D");


    //     $subs2d->setVisualVersion("2D");
    //     $subs2d->setAudioVersion("subtitles");

    //     $subs3d->setVisualVersion("3D");
    //     $subs3d->setAudioVersion("subtitles");

    //     $em->persist($dubbing2d);
    //     $em->persist($dubbing3d);
    //     $em->persist($subs2d);
    //     $em->persist($subs3d);

    //     $em->flush();


    //     return $this->render('movie_type/index.html.twig', [
    //         'controller_name' => 'MovieTypeController',
    //     ]);
    // }
}
