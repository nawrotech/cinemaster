<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OrderController extends AbstractController
{
    #[Route('/checkout', name: 'app_order_checkout')]
    public function checkout(
        #[Target('lemonSqueezyClient')]
        HttpClientInterface $lsClient,
        Request $request,
    ): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart');

        $lsCheckoutUrl = $this->createLsCheckoutUrl($lsClient, $cart);

        return $this->redirect($lsCheckoutUrl);
    }

    private function createLsCheckoutUrl(HttpClientInterface $lsClient, array $cart): string
    {
        if (empty($cart)) {
            throw new \LogicException('Nothing to checkout!');
        }

        $response = $lsClient->request(Request::METHOD_POST, 'checkouts', [
            'json' => [
                'data' => [
                    'type' => 'checkouts',
                    'relationships' => [
                        'store' => [
                            'data' => [
                                'type' => 'stores',
                                'id' => '179405',
                            ],
                        ],
                        'variant' => [
                            'data' => [
                                'type' => 'variants',
                                'id' => '806443'  
                            ]
                        ]
                    ],
                ],
            ],
        ]);

        // dd($response->getContent(false));

        $lsCheckout = $response->toArray();

        return $lsCheckout['data']['attributes']['url'];
    }
}
