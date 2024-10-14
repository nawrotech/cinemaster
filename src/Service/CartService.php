<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {
  
    public function addSeat(int $reservationSeatId, SessionInterface $session) {
        $cartSeats = $session->get('cart', []);

        if (!in_array($reservationSeatId, $cartSeats)) {
            $cartSeats[] = $reservationSeatId;
        }

        $session->set('cart', $cartSeats);
    }

    public function removeSeat(int $reservationSeatId, SessionInterface $session) {
        $cartSeats = $session->get('cart', []);

        $cartSeats = array_filter($cartSeats, fn($id) => $id !== $reservationSeatId);

        $session->set('cart', $cartSeats);
    }
}