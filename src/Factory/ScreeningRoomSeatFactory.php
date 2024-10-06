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

        $maxRow = $screeningRoom->getRowsMax();
        $maxSeatsPerRow = $screeningRoom->getRowsMax();
        $cinema = $screeningRoom->getCinema();

        $seats = CinemaSeatFactory::repository()->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s")
            ->addSelect("s")
            ->where('s.rowNum <= :maxRow')
            ->andWhere('s.colNum <= :maxSeatsPerRow')
            ->andWhere("cs.cinema = :cinema")
            ->setParameter('maxRow', $maxRow)
            ->setParameter('maxSeatsPerRow', $maxSeatsPerRow)
            ->setParameter('cinema', $cinema)
            ->getQuery()
            ->getResult();


        $screeningRoomSeats = [];
        foreach ($seats as $seat) {
            // Creating CinemaSeat records based on the cinema and each seat
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
