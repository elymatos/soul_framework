<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when frame instance is in invalid state
 */
class InvalidFrameInstanceStateException extends SoulException
{
    protected string $instanceId;
    protected string $currentState;
    protected string $expectedState;

    public function __construct(
        string $message,
        string $instanceId = '',
        string $currentState = '',
        string $expectedState = '',
        int $code = 400,
        ?Throwable $previous = null
    ) {
        $this->instanceId = $instanceId;
        $this->currentState = $currentState;
        $this->expectedState = $expectedState;

        $context = [
            'instance_id' => $instanceId,
            'current_state' => $currentState,
            'expected_state' => $expectedState
        ];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
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