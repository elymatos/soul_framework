<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when attempting to register a frame that already exists
 */
class FrameAlreadyExistsException extends SoulException
{
    protected string $frameId;

    public function __construct(string $frameId, int $code = 409, ?Throwable $previous = null)
    {
        $this->frameId = $frameId;

        $message = "Frame definition already exists: {$frameId}";
        $context = ['frame_id' => $frameId];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getFrameId(): string
    {
        return $this->frameId;
    }
}