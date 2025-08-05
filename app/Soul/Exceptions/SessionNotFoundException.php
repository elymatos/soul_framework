<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when session is not found
 */
class SessionNotFoundException extends ProcessingSessionException
{
    public function __construct(string $sessionId, int $code = 404, ?Throwable $previous = null)
    {
        $message = "Processing session not found: {$sessionId}";

        parent::__construct($message, $sessionId, $code, $previous);
    }
}