<?php

namespace App\Soul\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when cognitive processing times out
 */
class ProcessingTimeoutException extends CognitiveProcessingException
{
    protected int $timeoutMs;

    public function __construct(
        int $timeoutMs,
        string $processingStage = '',
        string $sessionId = '',
        int $code = 408,
        ?Throwable $previous = null
    ) {
        $this->timeoutMs = $timeoutMs;

        $message = "Cognitive processing timed out after {$timeoutMs}ms";

        $context = [
            'timeout_ms' => $timeoutMs,
            'processing_stage' => $processingStage,
            'session_id' => $sessionId
        ];

        parent::__construct($message, $processingStage, $sessionId, $code, $previous, $context);
    }

    public function getTimeoutMs(): int
    {
        return $this->timeoutMs;
    }
}
