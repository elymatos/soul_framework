<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when frame instantiation fails
 */
class FrameInstantiationException extends SoulException
{
    protected string $frameId;
    protected array $instantiationContext;

    public function __construct(
        string $message,
        string $frameId = '',
        array $instantiationContext = [],
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $this->frameId = $frameId;
        $this->instantiationContext = $instantiationContext;

        $context = [
            'frame_id' => $frameId,
            'instantiation_context' => $instantiationContext
        ];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getFrameId(): string
    {
        return $this->frameId;
    }

    public function getInstantiationContext(): array
    {
        return $this->instantiationContext;
    }
}