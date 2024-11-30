<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Repository\ReservationSeatRepository;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer {
    public function __construct(
        private MailerInterface $mailer,
        private ReservationSeatRepository $reservationSeatRepository,
        private BuilderInterface $qrBuilder,
        private QrCodeService $qrCodeService,
        )
    {
    }
    
    public function sendReservationReceipt(Reservation $reservation): void {

        $email = $reservation->getEmail();
        $reservationSeats = $this->reservationSeatRepository->findBy(["reservation" => $reservation]);
        $total = count($reservationSeats) * $reservation->getShowtime()->getPrice();

        $reservationQrCode = $this->qrCodeService->generateReservationQrCode($reservation);

        $email = (new TemplatedEmail())
            ->from("cinemaster@service.com")
            ->to(new Address($email))
            ->subject("Cinemaster - showtime reservation details")
            ->htmlTemplate('emails/showtime_receipt.html.twig')
             ->context([
                "reservation" => $reservation,
                "reservationSeats" => $reservationSeats,
                "total" => $total,
                "reservationQrCode" => $reservationQrCode
            ]);

        $this->mailer->send($email);

    }

    

}