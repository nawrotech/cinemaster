<?php 

namespace App\Tests\Validator;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use App\Repository\ShowtimeRepository;
use App\Validator\SameMoviePlayingInTwoRoomsAtTheSameTime;
use App\Validator\SameMoviePlayingInTwoRoomsAtTheSameTimeValidator;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class SameMoviePlayingInTwoRoomsAtTheSameTimeValidatorTest extends ConstraintValidatorTestCase 
{
    private $showtimeRepository;

    protected function createValidator(): ConstraintValidatorInterface
    {
        $this->showtimeRepository = $this->createMock(ShowtimeRepository::class);
        assert($this->showtimeRepository instanceof ShowtimeRepository);
        return new SameMoviePlayingInTwoRoomsAtTheSameTimeValidator($this->showtimeRepository);
    }
    
    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new SameMoviePlayingInTwoRoomsAtTheSameTime());
        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new SameMoviePlayingInTwoRoomsAtTheSameTime());
        $this->assertNoViolation();
    }
    
    public function testInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('anything', new class extends Constraint {});
    }
    
    public function testInvalidValueType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new \stdClass(), new SameMoviePlayingInTwoRoomsAtTheSameTime());
    }
    
    /**
     * @dataProvider provideValidShowtimes
     */
    public function testValidShowtimes(Showtime $showtime): void
    {
        // Mock repository to return empty array (no overlaps)
        $this->showtimeRepository
            ->expects($this->once())
            ->method('findOverlappingForMovie')
            ->willReturn([]);
            
        $this->validator->validate($showtime, new SameMoviePlayingInTwoRoomsAtTheSameTime());
        $this->assertNoViolation();
    }
    
    public function provideValidShowtimes(): \Generator
    {
        // Different times for the same movie
        yield 'different times' => [
            $this->createShowtime('14:00', '16:00', 'Avatar', 'Room A')
        ];
        
        // Different movie formats
        yield 'different formats' => [
            $this->createShowtime('14:00', '16:00', 'Avatar', 'Room A', 'IMAX')
        ];
        
        // Same movie but in different cinemas
        yield 'different cinemas' => [
            $this->createShowtime('14:00', '16:00', 'Avatar', 'Room A', 'Standard', 'Cinema 1')
        ];
    }
    
    /**
     * @dataProvider provideOverlappingShowtimes
     */
    public function testOverlappingShowtimes(
        Showtime $showtime, 
        array $existingShowtimes
    ): void {
        // Mock repository to return an array of overlapping showtimes
        $this->showtimeRepository
            ->expects($this->once())
            ->method('findOverlappingForMovie')
            ->willReturn($existingShowtimes);
            
        $constraint = new SameMoviePlayingInTwoRoomsAtTheSameTime();
        $this->validator->validate($showtime, $constraint);
        
        // The validator uses the first conflict for the error message
        $firstConflict = $existingShowtimes[0];
        
        $expectedDate = $firstConflict->getStartsAt()->format('d-m-Y');
        $expectedStartsAt = $firstConflict->getStartsAt()->format('H:i');
        $expectedEndsAt = $firstConflict->getEndsAt()->format('H:i');
        
        $this->buildViolation($constraint->message)
            ->setParameter('{{ movieTitle }}', $showtime->getMovieScreeningFormat()->getMovie()->getTitle())
            ->setParameter('{{ roomName }}', $firstConflict->getScreeningRoom()->getName())
            ->setParameter('{{ date }}', $expectedDate)
            ->setParameter('{{ startsAt }}', $expectedStartsAt)
            ->setParameter('{{ endsAt }}', $expectedEndsAt)
            ->assertRaised();
    }
    
    public function provideOverlappingShowtimes(): \Generator
    {
        // Same movie, same time, different rooms
        yield 'same time different rooms' => [
            $this->createShowtime('14:00', '16:00', 'Avatar', 'Room A'),
            [$this->createShowtime('14:00', '16:00', 'Avatar', 'Room B')]
        ];
        
        // Same movie, partially overlapping times
        yield 'partially overlapping' => [
            $this->createShowtime('14:00', '16:00', 'Avatar', 'Room A'),
            [$this->createShowtime('15:00', '17:00', 'Avatar', 'Room B')]
        ];
        
        // Same movie, one showing within another
        yield 'enclosed showtime' => [
            $this->createShowtime('13:00', '17:00', 'Avatar', 'Room A'),
            [$this->createShowtime('14:00', '16:00', 'Avatar', 'Room B')]
        ];
        
        // Multiple overlapping showtimes in different rooms
        yield 'multiple overlapping rooms' => [
            $this->createShowtime('13:50', '15:50', 'Avatar', 'Room C'),
            [
                $this->createShowtime('12:00', '14:00', 'Avatar', 'Room A'),
                $this->createShowtime('14:20', '16:20', 'Avatar', 'Room B')
            ]
        ];
    }
    
    /**
     * Helper method to create a showtime with given parameters
     */
    private function createShowtime(
        string $startsAt, 
        string $endsAt,
        string $movieTitle = 'Test Movie',
        string $roomName = 'Test Room',
        string $cinemaName = 'Test Cinema'
    ): Showtime {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        
        $movie = new Movie();
        $movie->setTitle($movieTitle);
        
        $movieScreeningFormat = new MovieScreeningFormat();
        $movieScreeningFormat->setMovie($movie);
  
        $cinema = new Cinema();
        $cinema->setName($cinemaName);
        
        $screeningRoom = new ScreeningRoom();
        $screeningRoom->setCinema($cinema);
        $screeningRoom->setName($roomName);
        
        $showtime = new Showtime();
        $showtime->setStartsAt(new DateTimeImmutable("$today $startsAt:00"));
        $showtime->setEndsAt(new DateTimeImmutable("$today $endsAt:00"));
        $showtime->setCinema($cinema);
        $showtime->setScreeningRoom($screeningRoom);
        $showtime->setMovieScreeningFormat($movieScreeningFormat);
        
       
        return $showtime;
    }
}