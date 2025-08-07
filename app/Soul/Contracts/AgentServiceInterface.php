<?php

namespace App\Soul\Contracts;

interface AgentServiceInterface
{
    /**
     * Execute an agent method with given parameters
     */
    public function executeAgent(string $method, array $parameters = []): mixed;
    
    /**
     * Get available agent methods for this service
     */
    public function getAvailableAgents(): array;
    
    /**
     * Validate parameters for a specific agent method
     */
    public function validateParameters(string $method, array $parameters): bool;
}