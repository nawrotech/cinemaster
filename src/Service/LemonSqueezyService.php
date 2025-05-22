<?php

namespace App\Service;

use App\Contract\PaymentProviderInterface;
use App\Entity\Showtime;
use App\Repository\ReservationSeatRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LemonSqueezyService implements PaymentProviderInterface
{
    public function __construct(
        #[Target('lemonSqueezyClient')]
        private HttpClientInterface $lsClient,
        #[Autowire(param: 'app.payment.lemon_squeezy.store_id')]
        private string $storeId,
        #[Autowire(param: 'app.payment.lemon_squeezy.product_ids')]
        private array $productIds,
        private ReservationSeatRepository $reservationSeatRepository,
        private CartService $cartService,
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack,
    ) {}

    public function createCheckout(Showtime $showtime, array $userData): string
    {
        $cartSeats = $this->cartService->getReservationSeatsForCheckout($showtime);

        $groupedItems = $this->groupItemsByPricingType($cartSeats);

        $attributes = [];

        $attributes['checkout_data']['custom'] = [(string) $showtime->getId()];

        $paymentToken = bin2hex(random_bytes(16));
        $session = $this->requestStack->getSession();
        $session->set('payment_token', $paymentToken);

        $attributes['product_options']['redirect_url'] = $this->urlGenerator
            ->generate('app_order_success', [
                'id' => $showtime->getId(),
                'payment_token' => $paymentToken
            ], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($userData) {
            $attributes['checkout_data']['email'] = $userData['email'];
        }

        if (count($groupedItems) > 1) {
            return $this->createMultiTypeCheckout($groupedItems, $attributes);
        }

        return $this->createSingleTypeCheckout($groupedItems, $attributes);
    }



    private function groupItemsByPricingType(array $cartItems): array
    {
        $grouped = [];

        foreach ($cartItems as $reservationSeat) {
            $priceTierType = $reservationSeat->getPriceTierType();

            $variantKey = $priceTierType ? $priceTierType->value : 'standard';

            if (!isset($grouped[$variantKey])) {
                $grouped[$variantKey] = [
                    'quantity' => 0,
                    'price' => $reservationSeat->getPriceTierPrice(),
                    'pricingTypeValue' => $reservationSeat->getPriceTierType()->value
                ];
            }

            $grouped[$variantKey]['quantity']++;
        }

        return $grouped;
    }

    private function createSingleTypeCheckout(array $groupedItems, array $attributes): string
    {
        $pricingType = array_key_first($groupedItems);
        $data = $groupedItems[$pricingType];

        $quantity = $data['quantity'];
        $variantId = $this->productIds[$pricingType] ?? $this->productIds['fallback'];

        $attributes['checkout_data']['variant_quantities'] = [
            [
                'variant_id' => (int) $variantId,
                'quantity' => $quantity,
            ],
        ];

        return $this->sendLemonSqueezyRequest($attributes, $variantId);
    }


    private function createMultiTypeCheckout(array $groupedItems, array $attributes): string
    {
        $total = 0;
        $description = '';
        foreach ($groupedItems as $type => $data) {

            $quantity = $groupedItems[$type]['quantity'];
            $pricingType = $groupedItems[$type]['pricingTypeValue'];
            $price = $groupedItems[$type]['price'];

            $total += $price * $quantity;

            $description .= sprintf(
                "%s x %d @ $%.2f each<br>",
                $pricingType,
                $quantity,
                $price
            );
        }

        $attributes['custom_price'] = (int)($total * 100);
        $attributes['product_options'] = [
            'name' => 'Cinema Tickets',
            'description' => $description,
        ];

        return $this->sendLemonSqueezyRequest($attributes, $this->productIds['fallback']);
    }


    private function sendLemonSqueezyRequest(array $attributes, string $variantId): string
    {
        $response = $this->lsClient->request(Request::METHOD_POST, 'checkouts', [
            'json' => [
                'data' => [
                    'type' => 'checkouts',
                    'attributes' => $attributes,
                    'relationships' => [
                        'store' => [
                            'data' => [
                                'type' => 'stores',
                                'id' => $this->storeId,
                            ],
                        ],
                        'variant' => [
                            'data' => [
                                'type' => 'variants',
                                'id' => $variantId,
                            ]
                        ]
                    ],
                ],
            ],
        ]);

        $checkout = $response->toArray();
        return $checkout['data']['attributes']['url'];
    }
}
