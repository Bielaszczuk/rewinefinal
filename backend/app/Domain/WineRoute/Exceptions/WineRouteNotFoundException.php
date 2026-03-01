<?php

namespace App\Domain\WineRoute\Exceptions;

use Exception;

class WineRouteNotFoundException extends Exception
{
    public function __construct(string $message = "Wine route not found", int $code = 404)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'timestamp' => now()->toISOString(),
            'path' => request()->path(),
            'status' => 404,
            'error' => 'Not Found',
            'message' => $this->getMessage(),
        ], 404);
    }
}

