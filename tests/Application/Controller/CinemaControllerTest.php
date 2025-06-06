<?php

namespace App\Tests;

use App\Entity\Cinema;
use App\Entity\User;
use App\Factory\CinemaFactory;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class CinemaControllerTest extends WebTestCase
{

    const EMAIL = "test@example.com";

    use Factories;

    private KernelBrowser $client;
    private User $user;
    private Cinema $cinema;


    protected function setUp(): void
    {

        $this->client = static::createClient();
        $this->initializeUser();
        $this->client->loginUser($this->user);
        $this->cinema = $this->initializeCinema();

    }


    private function initializeUser() {
        $this->user = UserFactory::createOne([
            "email" => $this::EMAIL
        ])->_real();
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

 

    public function testCinemaVisualFormatFormRedirectsDependingOnTheClickedButton(): void
    {      
        $cinema = $this->initializeCinema();
        $slug = $cinema->getSlug();

        $crawler = $this->client->request('GET', "/admin/cinemas/$slug/add-visual-formats");
        $form = $crawler->selectButton('Submit')->form();

        $form['cinema_visual_format_collection[visualFormats][0][name]'] = 'Format 1';

        $this->client->submit($form);
        $this->assertResponseRedirects("/admin/cinemas/$slug");

    }

}
