<?php

namespace App\RemoteEvent;

use App\Repository\ShowtimeRepository;
use App\Service\ReservationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('lemon-squeezy')]
final class LemonSqueezyWebhookConsumer implements ConsumerInterface
{
    public function __construct(
        private ReservationService $reservationService,
        private ShowtimeRepository $showtimeRepository,
        private RequestStack $requestStack,
    )
    {
    }

    public function consume(RemoteEvent $event): void
    {
        $payload = $event->getPayload();
        $showtimeId =  $payload['meta']['custom_data'][0];
        $email = $payload['data']['attributes']['user_email']; 

        if (!is_string($email)) {
            throw new \Exception('Wrong email you fool');
        }

        $showtime = $this->showtimeRepository->find($showtimeId);

        if (!$showtime) {
            throw new \Exception('Invalid argument exception!');
        }

        $this->reservationService->createReservation($showtime, $email);
    }
}
