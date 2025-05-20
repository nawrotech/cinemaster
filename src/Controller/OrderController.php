<?php

namespace App\Controller;

use App\Contract\PaymentProviderInterface;
use App\Entity\Showtime;
use App\Service\LemonSqueezyService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/checkout/showtimes/{id}', name: 'app_order_checkout')]
    public function checkout(
        #[Autowire(service: LemonSqueezyService::class)] PaymentProviderInterface $paymentProvider,
        Showtime $showtime
    ): Response
    {

        try {
            $checkoutUrl = $paymentProvider->createCheckout(
                $showtime, 
                $this->getUser(),
            );
            return $this->redirect($checkoutUrl);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Unable to create checkout: ' . $e->getMessage());
            return $this->redirectToRoute('app_reservation_reserve_showtime', [
                'slug' => $showtime->getCinema(),
                'showtime_slug' => $showtime->getSlug()
            ]);
        }
    }

}
