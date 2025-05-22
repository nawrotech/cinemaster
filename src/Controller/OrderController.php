<?php

namespace App\Controller;

use App\Contract\PaymentProviderInterface;
use App\Entity\Showtime;
use App\Service\CartService;
use App\Service\LemonSqueezyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
        RequestStack $requestStack
    ): Response {
        $redirectParams =  [
            'slug' => $showtime->getCinema()->getSlug(),
            'showtime_slug' => $showtime->getSlug()
        ];

        $sessionToken = $request->getSession()->get('payment_token');
        $requestToken = $request->query->get('payment_token');

        if (!$requestToken || $requestToken !== $sessionToken) {
            return $this->redirectToRoute('app_main_cinemas');
        }

        if ($cartService->isEmptyForShowtimeId($showtime->getId())) {
            return $this->redirectToRoute('app_reservation_reserve_showtime', $redirectParams);
        }

        $cartService->clearCartForShowtimeId($showtime->getId());
        $session = $requestStack->getSession();

        $session->remove('email');
        $session->remove('firstName');


        $this->addFlash('success', 'Thanks for the order, ticket has been sent to your email');

        return $this->redirectToRoute('app_reservation_reserve_showtime', $redirectParams);
    }
}
