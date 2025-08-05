<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when a frame instance is not found
 */
class FrameInstanceNotFoundException extends SoulException
{
    protected string $instanceId;

    public function __construct(string $message, string $instanceId = '', int $code = 404, ?Throwable $previous = null)
    {
        $this->instanceId = $instanceId;

        $context = ['instance_id' => $instanceId];
        $fullMessage = $message . ($instanceId ? " (Instance ID: {$instanceId})" : '');

        parent::__construct($fullMessage, $code, $previous, $context);
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }
}