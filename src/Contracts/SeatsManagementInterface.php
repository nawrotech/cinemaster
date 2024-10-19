<?php

namespace App\Contracts;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;

interface SeatsManagementInterface {

    public function storeChanges(Cinema|ScreeningRoom $cinema): void;


}