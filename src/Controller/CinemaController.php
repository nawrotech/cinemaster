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
        ?Cinema $cinema = null
    ): Response {

        if (!$cinema) {
            $cinema =  new Cinema();
            $cinema->setOwner($this->getUser());
        }
     
        $originalVisualFormats = new ArrayCollection();
        foreach($cinema->getVisualFormats() as $visualFormat) {
            $originalVisualFormats->add($visualFormat);
        }

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach($originalVisualFormats as $visualFormat) {
                if (false === $cinema->getVisualFormats()->contains($visualFormat)) {
                    $em->remove($visualFormat);
                }
            }

            $em->persist($cinema);
            $em->flush();            

            $this->addFlash("success", "Cinema created!");
            
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('cinema/create.html.twig', [
            "form" => $form
        ]);
    }

    





  
}
