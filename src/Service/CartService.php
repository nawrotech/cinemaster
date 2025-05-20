<?php

namespace App\Service;

use App\Entity\Showtime;
use App\Repository\ReservationSeatRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    public const CART_KEY = 'cart';
    public const ACTIVE_SHOWTIME_KEY = 'active_showtime';

    private SessionInterface $session;

    public function __construct(
        private ReservationSeatRepository $reservationSeatRepository,
        private RequestStack $requestStack
    ) {
        $this->session = $this->requestStack->getSession();
    }
  
    public function addSeat(int $reservationSeatId, int $showtimeId) {

        $cartSeats = $this->session->get('cart', []);

        if (!isset($cartSeats[$showtimeId])) {
            $cartSeats[$showtimeId] = [];
        }

        if (!in_array($reservationSeatId, $cartSeats[$showtimeId])) {
            $cartSeats[$showtimeId][] = $reservationSeatId;
        }

        $this->session->set('cart', $cartSeats);
    }

    public function removeSeat(int $reservationSeatId, int $showtimeId) {
        $cartSeats = $this->session->get('cart', []);
    
        if (isset($cartSeats[$showtimeId])) {
            $cartSeats[$showtimeId] = array_filter(
                $cartSeats[$showtimeId], 
                fn($id) => $id !== $reservationSeatId
            );
        }
        
        $this->session->set('cart', $cartSeats);
    }

    public function getReservationSeatsForCheckout(Showtime $showtime): array
    {
        $cart = $this->session->get('cart', []);

        $showtimeId = $showtime->getId();
        if (!isset($cart[$showtimeId]) || empty($cart[$showtimeId])) {
            return [];
        }

        $reservationSeats = $this->reservationSeatRepository->findBy(['id' => $cart[$showtimeId]]);

        $this->validateSeatsBelongToShowtime($reservationSeats, $showtimeId);

        return $reservationSeats;

    }


    private function validateSeatsBelongToShowtime(array $cartSeats, int $showtimeId): void {
        foreach ($cartSeats as $seat) {
            if ($seat->getShowtime()->getId() !== $showtimeId) {
                throw new \LogicException('Given seats dont\'t belong to given showtime!');
            }
        }
    }


    


}