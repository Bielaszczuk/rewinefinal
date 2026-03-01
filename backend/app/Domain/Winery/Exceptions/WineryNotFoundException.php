<?php

namespace App\Domain\Winery\Exceptions;

use Exception;

class WineryNotFoundException extends Exception
{
    public function __construct(string $message = "Winery not found", int $code = 404)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'timestamp' => now()->toISOString(),
            'path' => request()->path(),
            'requestId' => app()->bound('request-id') ? app('request-id') : null,
            'status' => 404,
            'code' => 'WINERY_NOT_FOUND',
            'message' => $this->getMessage(),
        ], 404);
    }
}
