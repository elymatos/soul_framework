<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when agent communication fails
 */
class AgentCommunicationException extends SoulException
{
    protected string $fromInstanceId;
    protected string $toInstanceId;
    protected string $method;

    public function __construct(
        string $message,
        string $fromInstanceId = '',
        string $toInstanceId = '',
        string $method = '',
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $this->fromInstanceId = $fromInstanceId;
        $this->toInstanceId = $toInstanceId;
        $this->method = $method;

        $context = [
            'from_instance_id' => $fromInstanceId,
            'to_instance_id' => $toInstanceId,
            'method' => $method
        ];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getFromInstanceId(): string
    {
        return $this->fromInstanceId;
    }

    public function getToInstanceId(): string
    {
        return $this->toInstanceId;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}