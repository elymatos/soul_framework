<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when agent is not authorized to communicate
 */
class AgentAuthorizationException extends AgentCommunicationException
{
    public function __construct(
        string $message,
        string $fromInstanceId = '',
        string $toInstanceId = '',
        string $method = '',
        int $code = 403,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $fromInstanceId, $toInstanceId, $method, $code, $previous);
    }
}