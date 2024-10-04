<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use App\Form\ShowtimeType;
use App\Repository\ShowtimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinema/{slug}/showtimes")]
class ShowtimeController extends AbstractController
{
    #[Route("/", name: "app_showtimes")]
    public function index(ShowtimeRepository $showtimeRepository): Response
    {

        return $this->render('showtime/index.html.twig', [
            "showtimes" => $showtimeRepository->findAll()
        ]);
    }

    #[Route("/create/{screening_room_slug}", name: "app_showtimes_create")]
    public function create(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])]
        ScreeningRoom $screeningRoom,
        EntityManagerInterface $em,
        ShowtimeRepository $showtimeRepository,
        Request $request,
    ): Response {

        $showtime = new Showtime();
        $showtime->setScreeningRoom($screeningRoom);

        $form = $this->createForm(ShowtimeType::class, $showtime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // dd($form->getData());
            // dd($showtimeRepository->findOverlapping(
            //     $showtime->getScreeningRoom(),
            //     $showtime->getStartTime(),
            //     $showtime->getEndTime()
            // ));

            $em->persist($showtime);
            $em->flush();

            return $this->redirectToRoute("app_showtimes", [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('showtime/create.html.twig', [
            "form" => $form
        ]);
    }
}
