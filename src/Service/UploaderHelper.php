<?php

namespace App\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderHelper {

    const MOVIE_IMAGE = "movie_image";
    const MOVIE_REFERENCE = "movie_reference";

    public function __construct(
        private SluggerInterface $slugger,
        private Packages $packages,
        private LoggerInterface $logger,
        private FilesystemOperator $remoteStorage,
        private CacheManager $cacheManager
    )
    {
    }    

    public function uploadMoviePoster(UploadedFile $file, ?string $existingPosterFilename): string {
        
        $newSafeFilename = $this->uploadFile($file, self::MOVIE_IMAGE, true);

        if ($existingPosterFilename) {
            try {
               $this->remoteStorage->delete(self::MOVIE_IMAGE . "/" . $existingPosterFilename);
            } catch(\Exception $e) {
                    $this->logger->alert("Old uploaded file was missing when trying to delete");
            }
         
        }
        return $newSafeFilename;
       
    }

    public function uploadMovieReference(File $file): string {
        return $this->uploadFile($file, self::MOVIE_REFERENCE, false);
    }

    /** 
     * @return resource
     */
    public function readStream(string $path) {
        try {
            $response = $this->remoteStorage->readStream($path);
        } catch (FilesystemException | UnableToReadFile $exception) {
            throw new \Exception($exception->getMessage());
        }

        return $response;
    }

    public function deleteFile(string $path) {

        try {
            $this->remoteStorage->delete($path);
        } catch (FilesystemException | UnableToDeleteFile $exception) {
        }

    }

    public function getFileMimeType(string $path): string {
        try {
            $mimeType = $this->remoteStorage->mimeType($path);
        } catch (FilesystemException | UnableToRetrieveMetadata $exception) {
        }

        return $mimeType;
    }

    public function uploadProfileImage(UploadedFile $file, ?string $existingFilePath): string {

        $profileImageFilename =  $this->uploadFile($file, self::MOVIE_IMAGE);

        if ($existingFilePath) {
            $this->cacheManager->remove($existingFilePath, "squared_thumbnail_small");
            $this->deleteFile($existingFilePath);
        }
        
        return $profileImageFilename;
   }

    private function uploadFile(File $file, string $directory, bool $isPublic = true) {

        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = $this->slugger->slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $newSafeFilename = $newFilename.'-'.uniqid().'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(), "r");
        $this->remoteStorage->writeStream(
            "$directory/$newSafeFilename",
            $stream,
            [
                "visibility" => $isPublic ? "public" : "private"
            ]
        );

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $newSafeFilename;
    }
}