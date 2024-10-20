<?php

namespace App\Factory;

use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ScreeningRoomSeat>
 */
final class ScreeningRoomSeatFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return ScreeningRoomSeat::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            // 'screeningRoom' => ScreeningRoomFactory::new(),
            // 'seatStatus' => self::faker()->text(100),
            // 'seatType' => self::faker()->text(100),
            // 'status' => self::faker()->text(15),
        ];
    }


    public static function createForScreeningRoom(ScreeningRoom $screeningRoom) {

        $cinema = $screeningRoom->getCinema();
        $maxRows = rand(7, $cinema->getMaxRows());
        $maxSeatsPerRow = rand(7, $cinema->getMaxSeatsPerRow());
        
        $seats = SeatFactory::repository()->createQueryBuilder('s')
            ->andWhere('s.rowNum <= :maxRows')
            ->andWhere('s.seatNumInRow <= :maxSeatsPerRow')
            ->setParameter('maxRows', $maxRows)
            ->setParameter('maxSeatsPerRow', $maxSeatsPerRow)
            ->getQuery()
            ->getResult();


        $screeningRoomSeats = [];
        foreach ($seats as $seat) {
            $screeningRoomSeats[] = self::createOne([
                'screeningRoom' => $screeningRoom,
                'seat' => $seat,
            ]);
        }
            return $screeningRoomSeats;
        }


    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ScreeningRoomSeat $screeningRoomSeat): void {})
        ;
    }
}
