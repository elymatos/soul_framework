<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when frame element operations fail
 */
class FrameElementException extends SoulException
{
    protected string $frameElementName;
    protected string $instanceId;

    public function __construct(
        string $message,
        string $frameElementName = '',
        string $instanceId = '',
        int $code = 400,
        ?Throwable $previous = null
    ) {
        $this->frameElementName = $frameElementName;
        $this->instanceId = $instanceId;

        $context = [
            'frame_element_name' => $frameElementName,
            'instance_id' => $instanceId
        ];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getFrameElementName(): string
    {
        return $this->frameElementName;
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }
}