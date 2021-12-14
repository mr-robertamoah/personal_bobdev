<?php

namespace App\Services;

use App\Exceptions\ServiceException;

class Service
{
    public function __callStatic($name, $arguments)
    {
        if (method_exists($class = static::class, $name)) {
            return (new static)->$name(...$arguments);
        }

        throw new ServiceException("{$name} method does not exist in the {$class} class");
    }
}