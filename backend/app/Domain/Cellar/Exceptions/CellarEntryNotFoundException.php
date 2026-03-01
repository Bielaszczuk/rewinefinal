<?php

namespace App\Domain\Cellar\Exceptions;

use Exception;

class CellarEntryNotFoundException extends Exception
{
    public function __construct(string $message = "Cellar entry not found", int $code = 404)
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

