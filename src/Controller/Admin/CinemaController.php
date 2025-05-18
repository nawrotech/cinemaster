<?php

namespace App\Controller\Admin;

use App\Entity\Cinema;
use App\Entity\PriceTier;
use App\Form\CinemaPriceTiersCollectionType;
use App\Form\CinemaScreeningFormatCollectionType;
use App\Form\CinemaScreeningRoomSetupCollectionType;
use App\Form\CinemaType;
use App\Form\CinemaVisualFormatCollectionType;
use App\Form\PriceTierType;
use App\Repository\CinemaRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSetupRepository;
use App\Repository\VisualFormatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/admin/cinemas")]
class CinemaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'app_cinema', methods: ['GET'])]
    public function index(
        CinemaRepository $cinemaRepository,
    ): Response {

        $cinemas = $cinemaRepository->findOrderedCinemas($this->getUser());

        return $this->render('cinema/index.html.twig', [
            "cinemas" => $cinemas,
        ]);
    }

    #[Route('/create', name: 'app_cinema_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
    ): Response {

        $cinema =  new Cinema();
        $cinema->setOwner($this->getUser());

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($cinema);
            $this->em->flush();

            $this->addFlash("success", "Cinema created successfully!");

            /** @var SubmitButton $addVisualsFormatsButton  */
            $addVisualsFormatsButton = $form->get("addVisualFormats");

            $routeName = $addVisualsFormatsButton->isClicked()
                ? "app_cinema_add_visual_formats"
                : "app_cinema_details";

            return $this->redirectToRoute($routeName, [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('cinema/create.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{slug}/add-visual-formats', name: 'app_cinema_add_visual_formats',  methods: ['GET', 'POST'])]
    public function addVisualFormats(
        Request $request,
        Cinema $cinema,
    ): Response {

        $form = $this->createForm(CinemaVisualFormatCollectionType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->flush();
            $this->addFlash("success", "Visual Formats have been added!");

            /** @var SubmitButton $addScreeningRoomSetupsButton */
            $addScreeningRoomSetupsButton =  $form->get("addScreeningRoomSetups");

            $routeName = $addScreeningRoomSetupsButton->isClicked()
                ? "app_cinema_add_screening_room_setups"
                : "app_cinema_details";

            return $this->redirectToRoute($routeName, [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('cinema/visual_formats_collection_form.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{slug}/add-screening-room-setups', name: 'app_cinema_add_screening_room_setups',  methods: ['GET', 'POST'])]
    public function addScreeningRoomSetups(
        Request $request,
        Cinema $cinema,
        VisualFormatRepository $visualFormatRepository,
    ): Response {

        $activeVisualFormats = $visualFormatRepository->findByCinemaAndActiveStatus($cinema, true);
        if (count($activeVisualFormats) < 1) {
            $this->addFlash("danger", "Add visual formats before adding screening room setups!");
            return $this->redirectToRoute("app_cinema_details", [
                "slug" => $cinema->getSlug()
            ]);
        }

        $form = $this->createForm(CinemaScreeningRoomSetupCollectionType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            /** @var SubmitButton $addScreeningFormatsButton */
            $addScreeningFormatsButton = $form->get("addScreeningFormats");
            $routeName = $addScreeningFormatsButton->isClicked()
                ? "app_cinema_add_screening_formats"
                : "app_cinema_details";

            $this->addFlash("success", "Screening room setups have been added!");

            return $this->redirectToRoute($routeName, [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('cinema/screening_room_setups_collection_form.html.twig', [
            "form" => $form,
            "activeVisualFormats" => $activeVisualFormats
        ]);
    }

    #[Route('/{slug}/add-price-tiers', name: 'app_cinema_add_price_tiers', methods: ['GET', 'POST'])]
    public function addPriceTiers(
        Cinema $cinema,
        Request $request, 
    ) {

        $form = $this->createForm(CinemaPriceTiersCollectionType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Price tier created successfully.');
            return $this->redirectToRoute('app_cinema_details', ['slug' => $cinema->getSlug()]);
        }

        return $this->render('cinema/price_tiers_collection_form.html.twig', [
            'cinema' => $cinema,
            'form' => $form
        ]);

    }

    #[Route('/{slug}/add-screening-formats', name: 'app_cinema_add_screening_formats',  methods: ['GET', 'POST'])]
    public function addScreeningFormats(
        Request $request,
        VisualFormatRepository $visualFormatRepository,
        Cinema $cinema,
    ): Response {

        $visualFormats = $visualFormatRepository->findByCinemaAndActiveStatus($cinema, true);
        if (count($visualFormats) < 1) {
            $this->addFlash("danger", "Add visual formats before adding screening formats!");
            return $this->redirectToRoute("app_cinema_details", [
                "slug" => $cinema->getSlug()
            ]);
        }

        $form = $this->createForm(CinemaScreeningFormatCollectionType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash("success", "Screening room setups has been added!");

            return $this->redirectToRoute("app_cinema_details", [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('cinema/screening_formats_collection_form.html.twig', [
            "form" => $form
        ]);
    }


    #[Route('/{slug}', name: 'app_cinema_details', methods: ['GET'])]
    public function cinemaDetails(
        Cinema $cinema,
        ScreeningRoomRepository $screeningRoomRepository,
        VisualFormatRepository $visualFormatRepository,
        ScreeningRoomSetupRepository $screeningRoomSetupRepository,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatRepository $movieScreeningFormatRepository
    ) {

        $visualFormats = $visualFormatRepository->findByCinemaAndActiveStatus($cinema, true);
        $screeningRoomSetups = $screeningRoomSetupRepository->findByCinemaAndActiveStatus($cinema, true);
        $screeningFormats = $screeningFormatRepository->findByCinemaAndActiveStatus($cinema, true);
        $screeningRooms = $screeningRoomRepository->findByCinemaAndActiveStatus($cinema, true);
        $movieVisualFormats = $movieScreeningFormatRepository->findMovieScreeningFormatsForCinema($cinema);

        return $this->render("cinema/cinema_details.html.twig", [
            "cinema" => $cinema,
            "visualFormats" => $visualFormats,
            "screeningRoomSetups" => $screeningRoomSetups,
            "screeningFormats" => $screeningFormats,
            "screeningRooms" => $screeningRooms,
            "movieVisualFormats" => $movieVisualFormats
        ]);
    }
}
