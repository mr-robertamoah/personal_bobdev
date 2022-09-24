<?php

namespace App\Traits;

trait HasUrlTrait
{
    public function url()
    {
        return $this->hasOne(Url::class);
    }

    public function getUrl()
    {
        return $this->url()->url;
    }
}