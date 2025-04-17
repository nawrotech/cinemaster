<?php 

// tests/Validator/ContainsAlphanumericValidatorTest.php
namespace App\Tests\Validator;

use App\Entity\Cinema;
use App\Entity\Showtime;
use App\Validator\ShowtimeSchedule;
use App\Validator\ShowtimeScheduleValidator;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use TypeError;

class ShowtimeScheduleValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ShowtimeScheduleValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new ShowtimeSchedule());
        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new ShowtimeSchedule());
        $this->assertNoViolation();
    }

    /**
     * @dataProvider provideValidShowtimes
     */
    public function testValidShowtimes(Showtime $showtime): void
    {
        $this->validator->validate($showtime, new ShowtimeSchedule());
        $this->assertNoViolation();
    }

    public function provideValidShowtimes(): \Generator
    {
        // Standard operating hours (10:00 - 23:00)
        yield 'standard: within operating hours' => [$this->createShowtime('10:30', '12:30', '10:00', '23:00')];
        yield 'standard: starts at opening' => [$this->createShowtime('10:00', '12:00', '10:00', '23:00')];
        yield 'standard: ends at closing' => [$this->createShowtime('21:00', '23:00', '10:00', '23:00')];
        
        // Overnight operating hours (14:00 - 02:00 next day)
        yield 'overnight: evening showtime' => [$this->createShowtime('14:30', '16:30', '14:00', '02:00')];
        yield 'overnight: early morning showtime' => [$this->createShowtime('00:30', '01:30', '14:00', '02:00')];
        yield 'overnight: spanning midnight' => [$this->createShowtime('23:00', '01:00', '14:00', '02:00')];
    }


    /**
     * @dataProvider provideInvalidShowtimes
     */
    public function testInvalidShowtimes(Showtime $showtime, string $expectedOperatingHours, string $expectedDuration): void
    {
        $constraint = new ShowtimeSchedule();
        
        $this->validator->validate($showtime, $constraint);
        
        $this->buildViolation('The show does NOT fit within operating hours. Operating hours: {{ cinemaOperatingHours }}. Showtime duration: {{ showtimeDuration }}.')
            ->setParameter('{{ cinemaOperatingHours }}', $expectedOperatingHours)
            ->setParameter('{{ showtimeDuration }}', $expectedDuration)
            ->assertRaised();
    }
    
    public function testInvalidConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('anything', new class extends Constraint {});
    }
    
    public function testInvalidValueType(): void
    {
        $this->expectException(TypeError::class);
        $this->validator->validate(new \stdClass(), new ShowtimeSchedule());
    }


    public function provideInvalidShowtimes(): \Generator
    {
        // Standard operating hours (10:00 - 23:00)
        yield 'standard: starts before opening' => [
            $this->createShowtime('09:00', '11:00', '10:00', '23:00'),
            '10:00 - 23:00',
            "09:00 - 11:00"
        ];
        
        yield 'standard: ends after closing' => [
            $this->createShowtime('22:00', '23:30', '10:00', '23:00'),
            '10:00 - 23:00',
            '22:00 - 23:30'
        ];
        
        yield 'standard: completely outside hours' => [
            $this->createShowtime('07:00', '09:00', '10:00', '23:00'),
            '10:00 - 23:00',
            '07:00 - 09:00'
        ];
        
        // // Overnight operating hours (14:00 - 02:00)
        yield 'overnight: starts before opening' => [
            $this->createShowtime('13:00', '15:00', '14:00', '02:00'),
            '14:00 - 02:00',
            '13:00 - 15:00'
        ];
        
        yield 'overnight: ends after closing' => [
            $this->createShowtime('01:00', '03:00', '14:00', '02:00'),
            '14:00 - 02:00',
            '01:00 - 03:00'
        ];
        
        yield 'overnight: in morning gap' => [
            $this->createShowtime('08:00', '10:00', '14:00', '02:00'),
            '14:00 - 02:00',
            '08:00 - 10:00'
        ];
    }
    
    /**
     * Tests specially for overnight operations
     */
    public function testOvernightOperationDetection(): void
    {
        // Valid: showtime spans past midnight but within overnight hours
        $validOvernight = $this->createShowtime('22:00', '01:00', '14:00', '02:00');
        $this->validator->validate($validOvernight, new ShowtimeSchedule());
        $this->assertNoViolation();
        
        // Invalid: showtime outside overnight hours
        $tooLate = $this->createShowtime('02:15', '03:30', '14:00', '02:00');
        $constraint = new ShowtimeSchedule();
        
        $this->validator->validate($tooLate, $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ cinemaOperatingHours }}', '14:00 - 02:00')
            ->setParameter('{{ showtimeDuration }}', '02:15 - 03:30')
            ->assertRaised();
    }
    
    /**
     * Tests that the violation parameters are correctly set
     */
    public function testViolationParametersAreSet(): void
    {
        $showtime = $this->createShowtime('09:00', '11:00', '10:00', '23:00');
        $constraint = new ShowtimeSchedule();
        
        $this->validator->validate($showtime, $constraint);
        
        $this->buildViolation($constraint->message)
            ->setParameter('{{ cinemaOperatingHours }}', '10:00 - 23:00')
            ->setParameter('{{ showtimeDuration }}', '09:00 - 11:00')
            ->assertRaised();
    }
    
    /**
     * Helper method to create a showtime with given times
     */
    private function createShowtime(
        string $startsAt, 
        string $endsAt, 
        string $cinemaOpenTime, 
        string $cinemaCloseTime
    ): Showtime {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        
        $showtime = new Showtime();
        $showtime->setStartsAt(new DateTimeImmutable("$today $startsAt:00"));
        $showtime->setEndsAt(new DateTimeImmutable("$today $endsAt:00"));
        
        $cinema = new Cinema();
        $cinema->setOpenTime(new DateTimeImmutable("$today $cinemaOpenTime:00"));
        $cinema->setCloseTime(new DateTimeImmutable("$today $cinemaCloseTime:00"));
        
        $showtime->setCinema($cinema);
        
        return $showtime;
    }
    

}