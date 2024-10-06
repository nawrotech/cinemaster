<?php

namespace App\Service;

class DateTimeService {

    public function __construct()
    {
    }
    
    public function checkIsStringInDateTimeFormat(string|null $dateTimeString) {

        $date = "2024-10-06";
        dd(strtotime($date));
        
        dd(date('Y-m-d', strtotime($date)) == $date);

        return (date('Y-m-d H:i:s', strtotime($dateTimeString)) == $dateTimeString);
    }

}