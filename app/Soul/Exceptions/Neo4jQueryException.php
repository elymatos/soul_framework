<?php

namespace App\Soul\Exceptions;

use Throwable;

/**
 * Exception thrown when Neo4j query fails
 */
class Neo4jQueryException extends Neo4jException
{
    protected string $cypherQuery;

    public function __construct(
        string $message,
        string $cypherQuery = '',
        array $queryParameters = [],
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $this->cypherQuery = $cypherQuery;

        $context = [
            'neo4j_operation' => 'query',
            'cypher_query' => $cypherQuery,
            'query_parameters' => $queryParameters
        ];

        parent::__construct($message, 'query', $queryParameters, $code, $previous);
        $this->setContext($context);
    }

    public function getCypherQuery(): string
    {
        return $this->cypherQuery;
    }
}