<?php

namespace App\Service;

use App\Enum\SeatPricing;
use App\Repository\ReservationSeatRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    public const CART_KEY = 'cart';
    public const ACTIVE_SHOWTIME_KEY = 'active_showtime';

    public function __construct(
        private ReservationSeatRepository $reservationSeatRepository,
        private SeatPricingService $seatPricingService
    ) {}
  
    public function addSeat(int $reservationSeatId, SessionInterface $session, int $showtimeId) {
        $cartSeats = $session->get('cart', []);

        if (!isset($cartSeats[$showtimeId])) {
            $cartSeats[$showtimeId] = [];
        }

        if (!in_array($reservationSeatId, $cartSeats[$showtimeId])) {
            $cartSeats[$showtimeId][] = $reservationSeatId;
        }

        $session->set('cart', $cartSeats);
    }

    public function removeSeat(int $reservationSeatId, SessionInterface $session, int $showtimeId) {
        $cartSeats = $session->get('cart', []);
    
        if (isset($cartSeats[$showtimeId])) {
            $cartSeats[$showtimeId] = array_filter(
                $cartSeats[$showtimeId], 
                fn($id) => $id !== $reservationSeatId
            );
        }
        
        $session->set('cart', $cartSeats);
    }


    public function getReservationSeats(array $seatIds): array
    {
        return $this->reservationSeatRepository->findBy(['id' => $seatIds]);
    }


    public function groupSeatsByPricingType(array $reservationSeats): array
    {
        $groupedSeats = [];
        
        foreach ($reservationSeats as $reservationSeat) {
            $pricingType = $reservationSeat->getSeat()->getPricingType()->value;
            $groupedSeats[$pricingType] = ($groupedSeats[$pricingType] ?? 0) + 1;
        }
        
        return $groupedSeats;
    }

    public function convertToCheckoutItems(array $groupedSeats): array
    {
        $checkoutItems = [];
        
        foreach ($groupedSeats as $pricingTypeValue => $quantity) {
            $pricingType = SeatPricing::tryFrom($pricingTypeValue);
            $productId = $this->seatPricingService->getProductIdForSeat($pricingType);
            $checkoutItems[] = [
                'type' => 'variants',
                'id' => $productId,
                'quantity' => $quantity,
            ];
        }
        
        return $checkoutItems;
    }

}