<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when agent method is not found
 */
class AgentMethodNotFoundException extends AgentCommunicationException
{
    public function __construct(
        string $toInstanceId,
        string $method,
        string $fromInstanceId = '',
        int $code = 404,
        ?Throwable $previous = null
    ) {
        $message = "Agent method '{$method}' not found in instance '{$toInstanceId}'";

        parent::__construct($message, $fromInstanceId, $toInstanceId, $method, $code, $previous);
    }
}