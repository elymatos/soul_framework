<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when frame element constraint validation fails
 */
class FrameElementConstraintViolationException extends FrameElementException
{
    protected array $violatedConstraints;
    protected mixed $attemptedValue;

    public function __construct(
        string $message,
        string $frameElementName = '',
        string $instanceId = '',
        array $violatedConstraints = [],
        mixed $attemptedValue = null,
        int $code = 400,
        ?Throwable $previous = null
    ) {
        $this->violatedConstraints = $violatedConstraints;
        $this->attemptedValue = $attemptedValue;

        $context = [
            'frame_element_name' => $frameElementName,
            'instance_id' => $instanceId,
            'violated_constraints' => $violatedConstraints,
            'attempted_value' => $attemptedValue
        ];

        parent::__construct($message, $frameElementName, $instanceId, $code, $previous);
        $this->setContext($context);
    }

    public function getViolatedConstraints(): array
    {
        return $this->violatedConstraints;
    }

    public function getAttemptedValue(): mixed
    {
        return $this->attemptedValue;
    }
}