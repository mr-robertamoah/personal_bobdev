<?php

namespace App\Actions;

class Action
{
    public static function make(): static
    {
        return new static;
    }
}