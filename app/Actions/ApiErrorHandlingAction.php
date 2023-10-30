<?php

namespace App\Actions;

use Throwable;

class ApiErrorHandlingAction extends Action
{
    public function execute(Throwable $throwable)
    {
        $statusCode = $throwable->getCode();
        $message = $throwable->getMessage();

        if ($statusCode < 200) $statusCode = 500;

        if ($throwable->getCode() == 0) $message = "Something unfortunate happened here. Please try again shortly.";

        return response()->json([
            "status" => false,
            "message" => $message,
        ], $statusCode);
    }
}