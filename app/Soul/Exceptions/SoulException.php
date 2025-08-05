<?php

namespace App\Soul\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for all SOUL framework related errors
 */
abstract class SoulException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context information
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set additional context information
     */
    public function setContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }

    /**
     * Get formatted error message with context
     */
    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();

        if (!empty($this->context)) {
            $contextStr = json_encode($this->context, JSON_PRETTY_PRINT);
            $message .= "\nContext: " . $contextStr;
        }

        return $message;
    }
}