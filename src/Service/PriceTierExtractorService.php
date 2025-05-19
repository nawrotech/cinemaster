<?php

namespace App\Service;

use App\Dto\ReservationPriceTierDto;
use App\Entity\Showtime;
use App\Repository\ReservationSeatRepository;

class PriceTierExtractorService
{

    public function __construct(private ReservationSeatRepository $reservationSeatRepository)
    {   
    }
    
    public function getShowtimePriceTiers(Showtime $showtime): array
    {

        $distinctPriceTiers = $this->reservationSeatRepository->findDistinctPriceTiersByShowtime($showtime);

        $priceTiers = [];
        foreach ($distinctPriceTiers as $priceTier) {
            $priceTiers[] = new ReservationPriceTierDto(
                $priceTier['priceTierName'],
                $priceTier['priceTierPrice'],
                $priceTier['priceTierColor']
            );
        }
        
        return $priceTiers;
    }
}