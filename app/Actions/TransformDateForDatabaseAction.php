<?php

namespace App\Actions;

use Carbon\Carbon;
use DateTime;

class TransformDateForDatabaseAction extends Action
{
    public function execute(string|DateTime|Carbon|null $date): string|null
    {
        if (is_null($date)) {
            return $date;
        }
        
        if (is_string($date) && Carbon::parse($date)->isValid()) {
            return Carbon::parse($date)->toDateTimeString();
        }
        
        if ($date instanceof Carbon) {
            return $date->toDateTimeString();
        }
        
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d H:i:s Z');
        }

        return null;
    }
}