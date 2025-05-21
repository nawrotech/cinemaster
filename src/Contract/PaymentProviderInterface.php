<?php

namespace App\Contract;

use App\Entity\Showtime;
use App\Entity\User;

interface PaymentProviderInterface
{
    public function createCheckout(Showtime $showtime, array $user): string;
}
