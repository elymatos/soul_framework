<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when a frame definition is not found
 */
class FrameNotFoundException extends SoulException
{
    protected string $frameId;

    public function __construct(string $message, string $frameId = '', int $code = 404, ?Throwable $previous = null)
    {
        $this->frameId = $frameId;

        $context = ['frame_id' => $frameId];
        $fullMessage = $message . ($frameId ? " (Frame ID: {$frameId})" : '');

        parent::__construct($fullMessage, $code, $previous, $context);
    }

    public function getFrameId(): string
    {
        return $this->frameId;
    }
}