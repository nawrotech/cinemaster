<?php

namespace App\Exception;

use InvalidArgumentException;

class InvalidRowsAndSeatsStructureException extends InvalidArgumentException
{
    protected $message = 'Seats per row array must be 1-based and sequential.';
}
