<?php

namespace App\Scheduler;

use App\Scheduler\Message\UnlockReservedSeats as MessageUnlockReservedSeats;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

// #[AsSchedule('unlock_reserved_seats')]
final class ReservedSeatsProvider //implements ScheduleProviderInterface
{
  

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->with(
                RecurringMessage::every("10 seconds", new MessageUnlockReservedSeats())
             
            )
        ;
    }
}
