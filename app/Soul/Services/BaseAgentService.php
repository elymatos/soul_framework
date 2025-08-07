<?php

namespace App\Soul\Services;

use App\Soul\Contracts\AgentServiceInterface;
use App\Soul\Contracts\GraphServiceInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseAgentService implements AgentServiceInterface
{
    protected GraphServiceInterface $graphService;
    protected array $agentMethods = [];
    
    public function __construct(GraphServiceInterface $graphService)
    {
        $this->graphService = $graphService;
        $this->initializeAgentMethods();
    }
    
    public function executeAgent(string $method, array $parameters = []): mixed
    {
        if (!$this->isValidAgent($method)) {
            throw new \Exception("Unknown agent method: {$method}");
        }
        
        if (!$this->validateParameters($method, $parameters)) {
            throw new \Exception("Invalid parameters for agent method: {$method}");
        }
        
        Log::info("Executing agent", [
            'service' => static::class,
            'method' => $method,
            'parameters_count' => count($parameters)
        ]);
        
        try {
            return $this->$method($parameters);
        } catch (\Exception $e) {
            Log::error("Agent execution failed", [
                'service' => static::class,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function getAvailableAgents(): array
    {
        return array_keys($this->agentMethods);
    }
    
    public function validateParameters(string $method, array $parameters): bool
    {
        if (!isset($this->agentMethods[$method])) {
            return false;
        }
        
        $requiredParams = $this->agentMethods[$method]['required_parameters'] ?? [];
        
        foreach ($requiredParams as $param) {
            if (!isset($parameters[$param])) {
                return false;
            }
        }
        
        return true;
    }
    
    protected function isValidAgent(string $method): bool
    {
        return method_exists($this, $method) && isset($this->agentMethods[$method]);
    }
    
    /**
     * Initialize agent methods registry - to be implemented by subclasses
     */
    abstract protected function initializeAgentMethods(): void;
    
    /**
     * Create standardized success response
     */
    protected function createSuccessResponse(array $data): array
    {
        return array_merge([
            'status' => 'success',
            'timestamp' => now()->toISOString(),
            'service' => class_basename(static::class)
        ], $data);
    }
    
    /**
     * Create standardized error response
     */
    protected function createErrorResponse(string $message, array $context = []): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'service' => class_basename(static::class)
        ];
    }
}