<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when required frame element is missing
 */
class RequiredFrameElementMissingException extends FrameElementException
{
    public function __construct(
        string $frameElementName,
        string $instanceId = '',
        int $code = 400,
        ?Throwable $previous = null
    ) {
        $message = "Required frame element '{$frameElementName}' is missing";

        parent::__construct($message, $frameElementName, $instanceId, $code, $previous);
    }
}