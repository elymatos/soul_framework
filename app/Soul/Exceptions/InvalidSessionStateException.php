<?php

namespace App\Soul\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when session is in invalid state
 */
class InvalidSessionStateException extends ProcessingSessionException
{
    protected string $currentState;
    protected string $expectedState;

    public function __construct(
        string $sessionId,
        string $currentState,
        string $expectedState,
        int $code = 400,
        ?Throwable $previous = null
    ) {
        $this->currentState = $currentState;
        $this->expectedState = $expectedState;

        $message = "Session '{$sessionId}' is in invalid state '{$currentState}', expected '{$expectedState}'";

        $context = [
            'session_id' => $sessionId,
            'current_state' => $currentState,
            'expected_state' => $expectedState
        ];

        parent::__construct($message, $sessionId, $code, $previous, $context);
    }

    public function getCurrentState(): string
    {
        return $this->currentState;
    }

    public function getExpectedState(): string
    {
        return $this->expectedState;
    }
}
