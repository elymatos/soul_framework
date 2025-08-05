<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when cognitive processing fails
 */
class CognitiveProcessingException extends SoulException
{
    protected string $processingStage;
    protected string $sessionId;

    public function __construct(
        string $message,
        string $processingStage = '',
        string $sessionId = '',
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $this->processingStage = $processingStage;
        $this->sessionId = $sessionId;

        $context = [
            'processing_stage' => $processingStage,
            'session_id' => $sessionId
        ];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getProcessingStage(): string
    {
        return $this->processingStage;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}