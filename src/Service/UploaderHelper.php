<?php

namespace App\Service;

use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploaderHelper {

    const MOVIE_IMAGE = "movie_image";
    const MOVIE_REFERENCE = "movie_reference";

    public function __construct(
        #[Autowire(service: "oneup_flysystem.uploads_filesystem_filesystem")]
        private Filesystem $filesystem,
        #[Autowire(param: "uploads_base_url")]
        private string $uploadedAssetsBaseUrl,
        private SluggerInterface $slugger,
        private Packages $packages,
        private LoggerInterface $logger,
    )
    {
    }    

    public function uploadMoviePoster(UploadedFile $file, ?string $existingPosterFilename): string {
        
        $newSafeFilename = $this->uploadFile($file, self::MOVIE_IMAGE, true);

        if ($existingPosterFilename) {
            try {
               $this->filesystem->delete(self::MOVIE_IMAGE . "/" . $existingPosterFilename);
            } catch(\Exception $e) {
                    $this->logger->alert("Old uploaded file was missing when trying to delete");
            }
         
        }
        return $newSafeFilename;
       

    }

    public function uploadMovieReference(File $file): string {
        return $this->uploadFile($file, self::MOVIE_REFERENCE, false);
    }

    public function getPublicPath(string $path): string {
        // determines if slash is needed before the path
        $fullPath = $this->uploadedAssetsBaseUrl . "/" . ltrim($path, "/");

        // if (strpos($fullPath, "://") !== false) {
        //     return $fullPath;
        // }

        return $this->packages->getUrl($fullPath);
    }

    /** 
     * @return resource
     */
    public function readStream(string $path) {

        return $this->filesystem->readStream($path);
    }

    public function deleteFile(string $path) {

        $result = $this->filesystem->delete($path);

        if ($result === false) {
            throw new \Exception("Error deleting $path");
        }


    }

    private function uploadFile(File $file, string $directory, bool $isPublic) {

        if ($file instanceof UploadedFile) {
            $originalFilename = $file->getClientOriginalName();
        } else {
            $originalFilename = $file->getFilename();
        }

        $newFilename = $this->slugger->slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $newSafeFilename = $newFilename.'-'.uniqid().'.'.$file->guessExtension();

        // $filesystem = $isPublic ? $this->publicUploadFilesystem : $this->privateUploadFilesystem;
        // writeStream decides about the veisibility now
        $filesystem = $this->filesystem;

        $stream = fopen($file->getPathname(), "r");
        $filesystem->writeStream(
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