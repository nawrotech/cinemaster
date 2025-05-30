<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\MovieReference;
use App\Service\UploaderHelper;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MovieReferenceController extends AbstractController
{

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/movie/{id}/references', name: 'app_movie_add_reference', methods: ["POST"])]
    public function uploadMovieReference(Movie $movie, Request $request, UploaderHelper $uploaderHelper, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get("reference");

        $violations = $validator->validate(
            $uploadedFile,
            [
                new File([
                    "maxSize" => "2M",
                    "mimeTypes" => [
                        "image/*",
                        "application/pdf",
                        "application/msword",
                        "application/vnd.ms-excel",
                        "text/plain"
                    ]
                    ]),
                    new NotBlank([
                        "message" => "Please select a file to upload"
                    ])
            ]
       
        );

        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        $filename = $uploaderHelper->uploadMovieReference($uploadedFile);

        $movieReference = new MovieReference($movie);
        $movieReference->setFilename($filename);
        $movieReference->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $movieReference->setMimeType($uploadedFile->getMimeType() ?? "application/octet-stream");

        $em->persist($movieReference);
        $em->flush();

        return  $this->json(
            $movieReference,
            201,
            [],
            [
                "groups" => ["main"]
            ]
        );        
    }

    #[Route("movie/{id}/references", name: "movie_references_list")]
    public function getMovieReferences(Movie $movie) 
    {
        return $this->json($movie->getMovieReferences(), 200, [], ["groups" => ["main"]]);
    }

    #[Route("movie/references/{id}/download", name: "movie_reference_download")]
    public function downloadMovieReference(
            MovieReference $movieReference, 
            S3Client $s3Client, 
            #[Autowire(env: "AWS_S3_BUCKET")]
            string $s3BucketName): Response  
    {

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $movieReference->getOriginalFilename(),
            "reference-" . uniqid()
        );

        $command = $s3Client->getCommand('GetObject', [
            'Bucket' => $s3BucketName,
            'Key' => $movieReference->getFilePath(),
            'ResponseContentType' => $movieReference->getMimeType(),
            'ResponseContentDisposition' => $disposition
        ]);

        $request = $s3Client->createPresignedRequest($command, "+30 minutes");

        return new RedirectResponse((string) $request->getUri());
  
    }

    #[Route("movie/references/{id}", name: "movie_reference_delete", methods: ["DELETE"])]
    public function deleteMovieReference(MovieReference $movieReference, UploaderHelper $uploaderHelper, EntityManagerInterface $em) {

        $uploaderHelper->deleteFile($movieReference->getFilePath());
        $em->remove($movieReference);
        $em->flush();

        return new Response(null, 204);
    }


    #[Route("movie/references/{id}", name: "movie_reference_update", methods: ["PUT"])]
    public function updateMovieReference(MovieReference $movieReference, UploaderHelper $uploaderHelper, EntityManagerInterface $em, SerializerInterface $serializer, Request $request, ValidatorInterface $validator) 
    {
        $serializer->deserialize(
            $request->getContent(),
            MovieReference::class,
            "json",
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $movieReference,
                "groups" => ["input"]
            ]
        );

        $em->flush();

        return $this->json(
            $movieReference,
            200,
            [],
            [
                "groups" => ["main"]
            ]
        );   
    }

    #[Route("movie/{id}/references/reorder", name: "movie_reference_reorder", methods: ["POST"])]
    public function reorderMovieReferences(Movie $movie, Request $request, EntityManagerInterface $em) {
        
        $orderedIds = json_decode($request->getContent(), true);
        if ($orderedIds === false) {
            return $this->json(["detail" => "Invalid body"], 400);
        }

        $orderedIds = array_flip($orderedIds);
        foreach ($movie->getMovieReferences() as $movieReference) {
            $movieReference->setPosition($orderedIds[$movieReference->getId()]);
        }

        $em->flush();

        return $this->json(
            $movie->getMovieReferences(),
            200,
            [],
            [
                "groups" => ["main"]
            ]
        );  
    }
}
