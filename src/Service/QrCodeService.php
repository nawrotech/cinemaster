<?php

namespace App\Service;

use App\Entity\Reservation;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QrCodeService
{

    public function __construct(
        private BuilderInterface $qrBuilder,
        private UrlGeneratorInterface $urlGenerator,
        private UriSigner $uriSigner,
    ) {}

    public function generateReservationQrCode(Reservation $reservation): string
    {
        $cinemaSlug = $reservation->getShowtime()->getCinema()->getSlug();
        $showtimeEndsAt = $reservation->getShowtime()->getEndsAt();
        $reservationId = $reservation->getId();

        $reservationValidationUrl = $this->urlGenerator->generate("app_reservation_ticket_validation_form", [
            "slug" => $cinemaSlug,
            "id" => $reservationId
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $signedUrl = $this->uriSigner->sign($reservationValidationUrl, $showtimeEndsAt);

        $qrCode = $this->qrBuilder->build(
            data: $signedUrl,
        )->getDataUri();

        return $qrCode;
    }
}
