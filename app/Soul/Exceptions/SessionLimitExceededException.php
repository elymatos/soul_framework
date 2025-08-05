<?php

namespace App\Soul\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when maximum concurrent sessions limit is reached
 */
class SessionLimitExceededException extends ProcessingSessionException
{
    protected int $currentSessionCount;
    protected int $maxSessions;

    public function __construct(
        int $currentSessionCount,
        int $maxSessions,
        int $code = 429,
        ?Throwable $previous = null
    ) {
        $this->currentSessionCount = $currentSessionCount;
        $this->maxSessions = $maxSessions;

        $message = "Session limit exceeded: {$currentSessionCount}/{$maxSessions}";

        $context = [
            'current_sessions' => $currentSessionCount,
            'max_sessions' => $maxSessions
        ];

        parent::__construct($message, '', $code, $previous, $context);
    }

    public function getCurrentSessionCount(): int
    {
        return $this->currentSessionCount;
    }

    public function getMaxSessions(): int
    {
        return $this->maxSessions;
    }
}
