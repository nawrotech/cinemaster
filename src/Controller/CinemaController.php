<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\CinemaSeat;
use App\Form\Type\CinemaType;
use App\Repository\CinemaRepository;
use App\Repository\SeatRepository;
use App\Service\CinemaChangeService;
use Doctrine\ORM\EntityManagerInterface;
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

        $cinemas = $cinemaRepository->findOrderedCinemas();

        return $this->render('cinema/index.html.twig', [
            "cinemas" => $cinemas
        ]);
    }


    #[Route('/create', name: 'app_cinema_create')]
    public function create(
        Request $request,
        SeatRepository $seatRepository,
        EntityManagerInterface $em
    ): Response {

        $cinema =  new Cinema();
        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $roomMaxSize = $form->get("screeningRoomSize");
            $maxRows = $roomMaxSize->get("maxRows")->getData();
            $maxSeatsPerRow = $roomMaxSize->get("maxSeatsPerRow")->getData();

            $seats = $seatRepository->findSeatsInRange(1, $maxRows, 1, $maxSeatsPerRow); 
         
            $em->wrapInTransaction(function($em) use($seats, $cinema) {
                foreach ($seats as $seat) {
                    $cinemaSeat = new CinemaSeat();
                    $cinemaSeat->setSeat($seat);
                    $cinemaSeat->setCinema($cinema);
    
                    $em->persist($cinemaSeat);
                }
    
                $em->persist($cinema);
                $em->flush();
            });

            $this->addFlash("success", "Cinema created!");
            
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('cinema/screening_room_max_size.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{slug}/edit', name: "app_cinema_edit")]
    public function edit(
        Cinema $cinema,
        Request $request,
        CinemaChangeService $cinemaChangeService
    ): Response {

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $cinemaChangeService->handleSeatsChange($cinema);
                $this->addFlash('success', 'Cinema updated successfully.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while updating the cinema.');
            } finally {
                return $this->redirectToRoute("app_cinema");
            }
        }

        return $this->render('cinema/edit.html.twig', [
            "form" => $form
        ]);
    }





  
}
