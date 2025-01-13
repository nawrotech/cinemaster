<?php

namespace App\Tests;

use App\Entity\Cinema;
use App\Entity\User;
use App\Factory\CinemaFactory;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class CinemaControllerTest extends WebTestCase
{

    const EMAIL = "m@n.com";

    use Factories;
    private KernelBrowser $client;
    private User $user;
    private Cinema $cinema;


    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createAuthenticatedClient($this::EMAIL);

    }

    private function initializeCinema(): Cinema {

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail($this::EMAIL);

        $cinema = CinemaFactory::createOne([
            "owner" => $user
        ]);
        assert($cinema instanceof Cinema);

        return $cinema;
    }

    private function createAuthenticatedClient(): KernelBrowser {

        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail($this::EMAIL);

        $client->loginUser($user);

        return $client;
    }


    public function testCinemaVisualFormatFormRedirectsDependingOnTheClickedButton(): void
    {

      
        $cinema = $this->initializeCinema();
        
        $slug = $cinema->getSlug();
        $crawler = $this->client->request('GET', "/admin/cinemas/$slug/add-visual-formats");

        $form = $crawler->selectButton('Add screening room setups')->form();

        $this->client->submit($form);
        $this->assertResponseRedirects("/admin/cinemas/$slug/add-screening-room-setups");

        $crawler = $this->client->request('GET', "/admin/cinemas/$slug/add-visual-formats");

        $form = $crawler->selectButton('Submit')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects("/admin/cinemas/$slug");

    }



    // protected function tearDown(): void
    // {
    //     parent::tearDown();

    //     $this->em->close();
    //     $this->em = null;
    // }
}
