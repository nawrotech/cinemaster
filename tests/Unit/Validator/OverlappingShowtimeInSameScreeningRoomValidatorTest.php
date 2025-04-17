<?php 

namespace App\Tests\Validator;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use App\Repository\ShowtimeRepository;
use App\Validator\OverlappingShowtimeInSameScreeningRoom;
use App\Validator\OverlappingShowtimeInSameScreeningRoomValidator;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class OverlappingShowtimeInSameScreeningRoomValidatorTest extends ConstraintValidatorTestCase 
{
    private $showtimeRepository;

    protected function createValidator(): ConstraintValidatorInterface
    {
        $this->showtimeRepository = $this->createMock(ShowtimeRepository::class);
        assert($this->showtimeRepository instanceof ShowtimeRepository);
        return new OverlappingShowtimeInSameScreeningRoomValidator($this->showtimeRepository);
    }
    
    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new OverlappingShowtimeInSameScreeningRoom());
        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new OverlappingShowtimeInSameScreeningRoom());
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
        $this->validator->validate(new \stdClass(), new OverlappingShowtimeInSameScreeningRoom());
    }
    
    /**
     * @dataProvider provideNoOverlappingShowtimes
     */
    public function testNoOverlappingShowtimes(Showtime $showtime, array $returnsFromRepository): void
    {
        // Mock repository to return data from provider
        $this->showtimeRepository
            ->expects($this->once())
            ->method('findOverlappingForRoom')
            ->willReturn($returnsFromRepository);
            
        $this->validator->validate($showtime, new OverlappingShowtimeInSameScreeningRoom());
        $this->assertNoViolation();
    }
    
    public function provideNoOverlappingShowtimes(): \Generator
    {
        // Testing with empty array (no overlaps)
        yield 'no overlaps found' => [
            $this->createShowtime('14:00', '16:00'),
            []
        ];
        
        // Non-overlapping before existing showtime
        yield 'non-overlapping before' => [
            $this->createShowtime('10:00', '12:00'),
            []
        ];
        
        // Non-overlapping after existing showtime
        yield 'non-overlapping after' => [
            $this->createShowtime('17:00', '19:00'),
            []
        ];
        
        // Adjacent but not overlapping
        yield 'adjacent to existing' => [
            $this->createShowtime('16:00', '18:00'),
            []
        ];
    }
    
    /**
     * @dataProvider provideOverlappingShowtimes
     */
    public function testOverlappingShowtimes(Showtime $showtime, array $overlappingShowtimes, string $expectedOverlaps): void
    {
        // Mock repository to return overlapping showtimes from provider
        $this->showtimeRepository
            ->expects($this->once())
            ->method('findOverlappingForRoom')
            ->willReturn($overlappingShowtimes);
            
        $constraint = new OverlappingShowtimeInSameScreeningRoom();
        $this->validator->validate($showtime, $constraint);
        
        $this->buildViolation($constraint->message)
            ->setParameter('{{ overlaps }}', $expectedOverlaps)
            ->assertRaised();
    }
    
    public function provideOverlappingShowtimes(): \Generator
    {
        // New showtime starts during existing
        $overlappingStartsDuring = $this->createShowtime('13:00', '15:00', 'Existing Movie');
        yield 'starts during existing' => [
            $this->createShowtime('14:30', '16:30'),
            [$overlappingStartsDuring],
            "\n- Existing Movie (13:00 - 15:00)"
        ];
        
        // New showtime ends during existing
        $overlappingEndsDuring = $this->createShowtime('14:00', '16:00', 'Existing Movie');
        yield 'ends during existing' => [
            $this->createShowtime('13:00', '15:00'),
            [$overlappingEndsDuring],
            "\n- Existing Movie (14:00 - 16:00)"
        ];
        
        // New showtime completely encompasses existing
        $overlappingEncompassed = $this->createShowtime('14:00', '16:00', 'Existing Movie');
        yield 'encompasses existing' => [
            $this->createShowtime('13:00', '17:00'),
            [$overlappingEncompassed],
            "\n- Existing Movie (14:00 - 16:00)"
        ];
        
        // New showtime completely within existing
        $overlappingContains = $this->createShowtime('14:00', '16:00', 'Existing Movie');
        yield 'within existing' => [
            $this->createShowtime('14:30', '15:30'),
            [$overlappingContains],
            "\n- Existing Movie (14:00 - 16:00)"
        ];
        
        // Multiple overlapping showtimes
        $overlappingShowtime1 = $this->createShowtime('15:00', '17:00', 'Movie A');
        $overlappingShowtime2 = $this->createShowtime('13:00', '15:00', 'Movie B');
        yield 'multiple overlaps' => [
            $this->createShowtime('14:00', '16:00'),
            [$overlappingShowtime1, $overlappingShowtime2],
            "\n- Movie A (15:00 - 17:00)\n- Movie B (13:00 - 15:00)"
        ];
    }
    
    /**
     * Helper method to create a showtime with given times
     */
    private function createShowtime(
        string $startsAt, 
        string $endsAt,
        string $movieTitle = 'Test Movie'
    ): Showtime {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        
        $movie = new Movie();
        $movie->setTitle($movieTitle);
        
        $movieScreeningFormat = new MovieScreeningFormat();
        $movieScreeningFormat->setMovie($movie);
        
        $cinema = new Cinema();
        $screeningRoom = new ScreeningRoom();
        $screeningRoom->setCinema($cinema);
        $screeningRoom->setName('Test Room');
        
        $showtime = new Showtime();
        $showtime->setStartsAt(new DateTimeImmutable("$today $startsAt:00"));
        $showtime->setEndsAt(new DateTimeImmutable("$today $endsAt:00"));
        $showtime->setCinema($cinema);
        $showtime->setScreeningRoom($screeningRoom);
        $showtime->setMovieScreeningFormat($movieScreeningFormat);
        
        return $showtime;
    }
}