<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Form\Type\CinemaType;
use App\Repository\CinemaRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

        $cinemas = $cinemaRepository->findOrderedCinemas($this->getUser());

        return $this->render('cinema/index.html.twig', [
            "cinemas" => $cinemas
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

    





  
}
