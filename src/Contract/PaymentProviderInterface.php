<?php

namespace App\Contract;

use App\Entity\Showtime;

interface PaymentProviderInterface
{
    public function createCheckout(Showtime $showtime, array $user): string;
}
