<?php

namespace App\Interfaces;

interface Request
{
    public function canMakeRequestFor(): array;
}