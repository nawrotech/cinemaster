<?php

namespace App\Controller;

use App\Contract\PaymentProviderInterface;
use App\Entity\Showtime;
use App\Service\CartService;
use App\Service\LemonSqueezyService;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/checkout/showtimes/{id}', name: 'app_order_checkout')]
    public function checkout(
        #[Autowire(service: LemonSqueezyService::class)] PaymentProviderInterface $paymentProvider,
        Showtime $showtime,
        Request $request,
        CartService $cartService
    ): Response {

        $session = $request->getSession();


        $userData = [
            'email' => $session->get('email'),
            'firstName' => $session->get('firstName')
        ];

        try {
            $checkoutUrl = $paymentProvider->createCheckout(
                $showtime,
                $userData
            );

            return $this->redirect($checkoutUrl);
        } catch (\Exception $e) {
            $cartService->clearCartForShowtimeId($showtime->getId());
            $this->addFlash('danger', 'Unable to create checkout: ' . $e->getMessage());
            return $this->redirectToRoute('app_reservation_reserve_showtime', [
                'slug' => $showtime->getCinema(),
                'showtime_slug' => $showtime->getSlug()
            ]);
        } 
    }

    #[Route('/checkout/success/showtimes/{id}', name: 'app_order_success')]
    public function success(
        Request $request,
        Showtime $showtime,
        CartService $cartService,
        ReservationService $reservationService,
    ): Response {
        $redirectParams =  [
            'slug' => $showtime->getCinema()->getSlug(),
            'showtime_slug' => $showtime->getSlug()
        ];

        $sessionToken = $request->getSession()->get('payment_token');
        $requestToken = $request->query->get('token');

        if (!$requestToken || $requestToken !== $sessionToken) {
            return $this->redirectToRoute('app_main_cinemas');
        }

        if ($cartService->isEmptyForShowtimeId($showtime->getId())) {
            return $this->redirectToRoute('app_reservation_reserve_showtime', $redirectParams);
        }

        $reservationService->createReservation($showtime);

        $this->addFlash('success', 'Thanks for the order, ticket has been sent to your email');

        return $this->redirectToRoute('app_reservation_reserve_showtime', $redirectParams);
    }
}
