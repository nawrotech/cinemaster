<?php

namespace App\Controller;

use App\Form\AudioFormatsType;
use App\Repository\AudioFormatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormatController extends AbstractController
{
    // this is for the cinema
    #[Route('/format', name: 'app_format')]
    public function index(
        Request $request,
        AudioFormatRepository $audioFormatRepository,
        EntityManagerInterface $em): Response
    {

        

        $form = $this->createForm(AudioFormatsType::class, $audioFormatRepository->findAll());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

         
            foreach($form->get("audioFormats")->getData() as $audioFormat) {
                $em->persist($audioFormat);
            }

            $em->flush();            

            $this->addFlash("success", "Visual formats have been created!");
            
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('format/index.html.twig', [
            "form" => $form
        ]);
    }
}
