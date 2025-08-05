<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when frame definition is invalid or malformed
 */
class InvalidFrameDefinitionException extends SoulException
{
    protected string $frameId;
    protected array $validationErrors = [];

    public function __construct(
        string $message,
        string $frameId = '',
        array $validationErrors = [],
        int $code = 400,
        ?Throwable $previous = null
    ) {
        $this->frameId = $frameId;
        $this->validationErrors = $validationErrors;

        $context = [
            'frame_id' => $frameId,
            'validation_errors' => $validationErrors
        ];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getFrameId(): string
    {
        return $this->frameId;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}