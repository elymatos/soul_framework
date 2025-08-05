<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when processing session operations fail
 */
class ProcessingSessionException extends SoulException
{
    protected string $sessionId;

    public function __construct(
        string $message,
        string $sessionId = '',
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $this->sessionId = $sessionId;

        $context = ['session_id' => $sessionId];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}