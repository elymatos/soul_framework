<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when Neo4j operations fail
 */
class Neo4jException extends SoulException
{
    protected string $operation;
    protected array $queryParameters;

    public function __construct(
        string $message,
        string $operation = '',
        array $queryParameters = [],
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $this->operation = $operation;
        $this->queryParameters = $queryParameters;

        $context = [
            'neo4j_operation' => $operation,
            'query_parameters' => $queryParameters
        ];

        parent::__construct($message, $code, $previous, $context);
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }
}