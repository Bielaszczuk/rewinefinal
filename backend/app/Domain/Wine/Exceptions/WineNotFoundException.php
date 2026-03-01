<?php

namespace App\Domain\Wine\Exceptions;

use Exception;

class WineNotFoundException extends Exception
{
    public function __construct(string $message = "Wine not found", int $code = 404)
    {
        parent::__construct($message, $code);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'timestamp' => now()->toISOString(),
            'path' => request()->path(),
            'requestId' => app()->bound('request-id') ? app('request-id') : null,
            'status' => 404,
            'code' => 'WINE_NOT_FOUND',
            'message' => $this->getMessage(),
        ], 404);
    }
}
