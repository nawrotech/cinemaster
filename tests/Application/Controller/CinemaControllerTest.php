<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CinemaControllerTest extends WebTestCase
{
    public function testNavbarAndFooterAreDisplayed(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $testUser = $userRepository->findOneByEmail('m@n.com');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/cinemas/');
        $this->assertSelectorExists('nav');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', 'Cinemaster');


    }
}
