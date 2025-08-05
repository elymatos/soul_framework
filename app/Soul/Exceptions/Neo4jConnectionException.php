<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when Neo4j connection fails
 */
class Neo4jConnectionException extends Neo4jException
{
    public function __construct(string $message, int $code = 503, ?Throwable $previous = null)
    {
        parent::__construct($message, 'connection', [], $code, $previous);
    }
}