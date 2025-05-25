<?php

namespace App\Tests\Application\Controller;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningFormat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use App\Entity\VisualFormat;
use App\Factory\CinemaFactory;
use App\Factory\MovieFactory;
use App\Factory\MovieScreeningFormatFactory;
use App\Factory\ScreeningFormatFactory;
use App\Factory\ScreeningRoomFactory;
use App\Factory\ScreeningRoomSetupFactory;
use App\Factory\UserFactory;
use App\Factory\VisualFormatFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class ShowtimeControllerTest extends WebTestCase
{
    use Factories;
    
    public function testSuccessfulShowtimeSubmission(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $admin = UserFactory::createOne();
        $client->loginUser($admin->_real());


        $cinema = CinemaFactory::createOne([
            'owner' => $admin,
            'openTime' => new \DateTimeImmutable('08:00'),
            'closeTime' => new \DateTimeImmutable('23:00')
        ]);

     
        $visualFormat = VisualFormatFactory::createOne([
            'name' => '2D Visual Format',
            'cinema' => $cinema
        ]);
        
       
        $screeningFormat = ScreeningFormatFactory::createOne([
            'cinema' => $cinema,
            'visualFormat' => $visualFormat
        ]);
        
 
        $screeningRoomSetup = ScreeningRoomSetupFactory::createOne([
            'cinema' => $cinema,
            'visualFormat' => $visualFormat
        ]);
        
   
        $screeningRoom = ScreeningRoomFactory::createOne([
            'name' => 'test room',
            'slug' => 'test-room',
            'cinema' => $cinema,
            'screeningRoomSetup' => $screeningRoomSetup
        ]);

      
        $movie = MovieFactory::createOne([
            'title' => 'Test Movie',
            'slug' => 'test-movie'
        ]);
        
        $movieScreeningFormat = MovieScreeningFormatFactory::createOne([
            'movie' => $movie,
            'screeningFormat' => $screeningFormat
        ]);
        
       
        $entityManager->flush();
        
        $crawler = $client->request('GET', sprintf(
            '/admin/cinemas/%s/screening-rooms/%s/showtimes/create',
            $cinema->getSlug(),
            $screeningRoom->getSlug()
        ));

        $this->assertResponseIsSuccessful();

    
        $form = $crawler->selectButton('Submit')->form();
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $tomorrowFormatted = $tomorrow->format('Y-m-d');
        
        $form['showtime[movieScreeningFormat]'] = $movieScreeningFormat->getId();
        $form['showtime[price]'] = '12.99';
        $form['showtime[advertisementTimeInMinutes]'] = '15';
        $form['showtime[startsAt]'] = $tomorrowFormatted . 'T14:00:00';
        
        $client->submit($form);

        $this->assertResponseRedirects();

        $client->followRedirect();

        $this->assertStringContainsString(
            'showtimeStarting=' . $tomorrowFormatted, 
            $client->getRequest()->getUri(),
            'Redirect URL does not contain the expected date parameter'
        );

        $this->assertSelectorTextContains('.alert-success', 'Showtime created successfully');
        $repository = $entityManager->getRepository(Showtime::class);
        $showtime = $repository->findOneBy([
            'screeningRoom' => $screeningRoom->getId(),
        ]);

        $this->assertNotNull($showtime, 'Showtime was not created in the database');

        $this->assertEquals($tomorrowFormatted, $showtime->getStartsAt()->format('Y-m-d'));


        
    }

 
}
