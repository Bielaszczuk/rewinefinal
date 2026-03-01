<?php

namespace App\Domain\User\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct(string $message = "User not found", int $code = 404)
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
            'code' => 'USER_NOT_FOUND',
            'message' => $this->getMessage(),
        ], 404);
    }
}
